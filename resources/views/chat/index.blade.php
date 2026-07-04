@extends('Layouts.app')
@section('title', 'Encrypted Chat')

@section('content')
<div class="dashboard-main">
    <div class="sidebar-form" style="width:300px;">
        <div class="sidebar-card">
            <div class="sidebar-title"><i class="fas fa-lock"></i> Contacts</div>
            <div class="form-group" style="margin-bottom:10px;">
                <input type="text" id="contactSearch" placeholder="Search by name or email..." autocomplete="off">
            </div>
            <div id="contactList">
                @forelse($contacts as $contact)
                    <div class="contact-row" data-id="{{ $contact->id }}" data-name="{{ $contact->name }}"
                         data-has-key="{{ $contact->chat_public_key ? '1' : '0' }}"
                         style="padding:10px 12px; border-radius:8px; cursor:pointer; margin-bottom:6px; display:flex; align-items:center; justify-content:space-between; border:1px solid var(--border);">
                        <div>
                            <div style="font-weight:600; font-size:0.9rem;">{{ $contact->name }}</div>
                            <div class="text-muted" style="font-size:0.75rem;">{{ $contact->email }}</div>
                        </div>
                        @if(!$contact->chat_public_key)
                            <span class="badge" style="background:rgba(148,163,184,.2); color:var(--muted);" title="This user hasn't opened Chat yet">
                                <i class="fas fa-clock"></i>
                            </span>
                        @endif
                    </div>
                @empty
                    <p class="text-muted">No other registered users yet.</p>
                @endforelse
            </div>
            <p id="noContactsFound" class="text-muted" style="display:none; margin-top:8px;">No matching users found.</p>
        </div>
    </div>

    <div class="main-content">
        <div class="table-card" style="flex:1; display:flex; flex-direction:column;">
            <div class="table-header">
                <div class="table-title" id="chatTitle"><i class="fas fa-comments"></i> Select a contact</div>
                <span id="e2eBadge" class="badge badge-enc"><i class="fas fa-lock"></i> End-to-end encrypted</span>
            </div>

            <div id="messageWindow" style="flex:1; overflow-y:auto; display:flex; flex-direction:column; gap:10px; padding:12px 4px; min-height:340px;">
                <p class="text-muted text-center" id="emptyState">Choose a contact on the left to start an encrypted conversation.</p>
            </div>

            <form id="sendForm" style="display:none; gap:10px; margin-top:14px; align-items:flex-end;">
                <div class="form-group" style="flex:1; margin-bottom:0;">
                    <input type="text" id="messageInput" placeholder="Type an encrypted message…" autocomplete="off">
                </div>
                <button type="submit" class="btn btn-primary" style="height:52px;">
                    <i class="fas fa-paper-plane"></i> Send
                </button>
            </form>

            <p class="text-muted" style="font-size:0.75rem; margin-top:10px;">
                <i class="fas fa-circle-info"></i>
                Messages are encrypted in your browser before they ever leave it. Your private key lives only in this browser's local storage — the server only ever stores unreadable ciphertext.
            </p>
        </div>
    </div>
</div>

<script>
(function () {
    const ME_ID = {{ $me->id }};
    const PRIV_KEY_STORAGE = 'sfss_chat_private_key_jwk';
    const PUB_KEY_STORAGE  = 'sfss_chat_public_key_jwk';

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    let myPrivateKey = null;   // CryptoKey
    let myPublicJwk  = null;   // JWK object (own)
    let activeContact = null;
    let pollTimer = null;
    const publicKeyCache = {}; // userId -> CryptoKey

    // ── base64 helpers ───────────────────────────────────────────────────────
    function bufToB64(buf) {
        return btoa(String.fromCharCode(...new Uint8Array(buf)));
    }
    function b64ToBuf(b64) {
        const bin = atob(b64);
        const bytes = new Uint8Array(bin.length);
        for (let i = 0; i < bin.length; i++) bytes[i] = bin.charCodeAt(i);
        return bytes.buffer;
    }

    // ── Key setup: generate once per browser, reuse afterwards ──────────────
    async function ensureKeyPair() {
        const storedPriv = localStorage.getItem(PRIV_KEY_STORAGE);
        const storedPub  = localStorage.getItem(PUB_KEY_STORAGE);

        if (storedPriv && storedPub) {
            myPrivateKey = await crypto.subtle.importKey(
                'jwk', JSON.parse(storedPriv),
                { name: 'RSA-OAEP', hash: 'SHA-256' },
                true, ['decrypt']
            );
            myPublicJwk = JSON.parse(storedPub);
            // Make sure the server has our current public key on file.
            await uploadPublicKey(myPublicJwk);
            return;
        }

        const keyPair = await crypto.subtle.generateKey(
            { name: 'RSA-OAEP', modulusLength: 2048, publicExponent: new Uint8Array([1, 0, 1]), hash: 'SHA-256' },
            true,
            ['encrypt', 'decrypt']
        );

        const privJwk = await crypto.subtle.exportKey('jwk', keyPair.privateKey);
        const pubJwk  = await crypto.subtle.exportKey('jwk', keyPair.publicKey);

        localStorage.setItem(PRIV_KEY_STORAGE, JSON.stringify(privJwk));
        localStorage.setItem(PUB_KEY_STORAGE, JSON.stringify(pubJwk));

        myPrivateKey = keyPair.privateKey;
        myPublicJwk = pubJwk;

        await uploadPublicKey(pubJwk);
    }

    async function uploadPublicKey(jwk) {
        await fetch("{{ route('chat.public-key') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ public_key: JSON.stringify(jwk) }),
        });
    }

    async function importPublicKey(userId, jwkString) {
        if (publicKeyCache[userId]) return publicKeyCache[userId];
        const jwk = JSON.parse(jwkString);
        const key = await crypto.subtle.importKey(
            'jwk', jwk, { name: 'RSA-OAEP', hash: 'SHA-256' }, true, ['encrypt']
        );
        publicKeyCache[userId] = key;
        return key;
    }

    // ── Sending ──────────────────────────────────────────────────────────────
    async function sendMessage(recipientId, recipientPubKeyString, text) {
        const aesKey = await crypto.subtle.generateKey({ name: 'AES-GCM', length: 256 }, true, ['encrypt', 'decrypt']);
        const iv = crypto.getRandomValues(new Uint8Array(12));

        const ciphertextBuf = await crypto.subtle.encrypt(
            { name: 'AES-GCM', iv }, aesKey, new TextEncoder().encode(text)
        );

        const rawAesKey = await crypto.subtle.exportKey('raw', aesKey);

        const recipientPubKey = await importPublicKey(recipientId, recipientPubKeyString);
        const myPubKey = await crypto.subtle.importKey(
            'jwk', myPublicJwk, { name: 'RSA-OAEP', hash: 'SHA-256' }, true, ['encrypt']
        );

        const keyForRecipient = await crypto.subtle.encrypt({ name: 'RSA-OAEP' }, recipientPubKey, rawAesKey);
        const keyForSender    = await crypto.subtle.encrypt({ name: 'RSA-OAEP' }, myPubKey, rawAesKey);

        const res = await fetch(`/chat/${recipientId}/messages`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({
                ciphertext: bufToB64(ciphertextBuf),
                iv: bufToB64(iv),
                key_for_recipient: bufToB64(keyForRecipient),
                key_for_sender: bufToB64(keyForSender),
            }),
        });

        if (!res.ok) {
            const data = await res.json().catch(() => ({}));
            throw new Error(data.error || 'Failed to send message.');
        }
    }

    // ── Receiving / decrypting ──────────────────────────────────────────────
    async function decryptMessage(msg) {
        const rawAesKey = await crypto.subtle.decrypt({ name: 'RSA-OAEP' }, myPrivateKey, b64ToBuf(msg.encrypted_key));
        const aesKey = await crypto.subtle.importKey('raw', rawAesKey, { name: 'AES-GCM' }, false, ['decrypt']);
        const plainBuf = await crypto.subtle.decrypt(
            { name: 'AES-GCM', iv: new Uint8Array(b64ToBuf(msg.iv)) }, aesKey, b64ToBuf(msg.ciphertext)
        );
        return new TextDecoder().decode(plainBuf);
    }

    // ── UI wiring ────────────────────────────────────────────────────────────
    const messageWindow = document.getElementById('messageWindow');
    const sendForm = document.getElementById('sendForm');
    const messageInput = document.getElementById('messageInput');
    const chatTitle = document.getElementById('chatTitle');
    const contactSearchInput = document.getElementById('contactSearch');
    const noContactsFound = document.getElementById('noContactsFound');

    function renderBubble(text, isMine, timestamp) {
        const row = document.createElement('div');
        row.style.display = 'flex';
        row.style.justifyContent = isMine ? 'flex-end' : 'flex-start';

        const bubble = document.createElement('div');
        bubble.style.maxWidth = '65%';
        bubble.style.padding = '10px 14px';
        bubble.style.borderRadius = '14px';
        bubble.style.fontSize = '0.9rem';
        bubble.style.background = isMine ? 'var(--accent)' : 'var(--card2)';
        bubble.style.color = isMine ? '#fff' : 'var(--text)';
        bubble.textContent = text;

        row.appendChild(bubble);
        messageWindow.appendChild(row);
    }

    async function loadConversation(contactId, contactPubKey) {
        const res = await fetch(`/chat/${contactId}/messages`, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();

        messageWindow.innerHTML = '';
        if (!data.messages.length) {
            const p = document.createElement('p');
            p.className = 'text-muted text-center';
            p.textContent = 'No messages yet — say hello!';
            messageWindow.appendChild(p);
        }

        for (const msg of data.messages) {
            try {
                const text = await decryptMessage(msg);
                renderBubble(text, msg.sender_id === ME_ID, msg.created_at);
            } catch (e) {
                renderBubble('[unable to decrypt this message]', msg.sender_id === ME_ID, msg.created_at);
            }
        }
        messageWindow.scrollTop = messageWindow.scrollHeight;
    }

    const publicKeyStrings = {}; // userId -> JWK string (raw, as stored on server)

    async function refreshPublicKeyStrings() {
        const res = await fetch("{{ route('chat.public-keys') }}", { headers: { 'Accept': 'application/json' } });
        Object.assign(publicKeyStrings, await res.json());
    }

    document.querySelectorAll('.contact-row').forEach(row => {
        row.addEventListener('click', async () => {
            const id = row.dataset.id;
            const name = row.dataset.name;
            const hasKey = row.dataset.hasKey === '1';

            document.querySelectorAll('.contact-row').forEach(r => r.style.borderColor = 'var(--border)');
            row.style.borderColor = 'var(--accent)';

            activeContact = { id, name, hasKey };
            chatTitle.innerHTML = `<i class="fas fa-comments"></i> ${name}`;
            document.getElementById('emptyState').style.display = 'none';
            sendForm.style.display = hasKey ? 'flex' : 'none';

            if (!hasKey) {
                messageWindow.innerHTML = '<p class="text-muted text-center">This user hasn\'t opened Chat yet, so they don\'t have an encryption key set up. Ask them to open the Chat page once.</p>';
                return;
            }

            clearInterval(pollTimer);
            await refreshPublicKeyStrings();
            await loadConversation(id);
            pollTimer = setInterval(() => loadConversation(id), 4000);
        });
    });

    function filterContacts() {
        const keyword = (contactSearchInput?.value || '').trim().toLowerCase();
        let visibleCount = 0;

        document.querySelectorAll('.contact-row').forEach(row => {
            const name = (row.dataset.name || '').toLowerCase();
            const email = (row.querySelector('.text-muted')?.textContent || '').toLowerCase();
            const isMatch = !keyword || name.includes(keyword) || email.includes(keyword);

            row.style.display = isMatch ? 'flex' : 'none';
            if (isMatch) visibleCount++;
        });

        if (noContactsFound) {
            noContactsFound.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    if (contactSearchInput) {
        contactSearchInput.addEventListener('input', filterContacts);
    }

    sendForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const text = messageInput.value.trim();
        if (!text || !activeContact) return;

        messageInput.value = '';
        try {
            if (!publicKeyStrings[activeContact.id]) await refreshPublicKeyStrings();
            await sendMessage(activeContact.id, publicKeyStrings[activeContact.id], text);
            await loadConversation(activeContact.id);
        } catch (err) {
            alert(err.message);
        }
    });

    // ── Boot ─────────────────────────────────────────────────────────────────
    ensureKeyPair().catch(err => {
        console.error(err);
        messageWindow.innerHTML = '<p class="text-muted text-center">Could not set up encryption keys in this browser.</p>';
    });
})();
</script>
@endsection
