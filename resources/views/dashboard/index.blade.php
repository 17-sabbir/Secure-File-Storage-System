@extends('Layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="dashboard-main">

    {{-- LEFT SIDEBAR --}}
    <div class="sidebar-form">
        <div class="sidebar-card">
            {{-- Form Title --}}
            <div class="sidebar-title">
                 <img src="{{ asset('cryptologo.png') }}" style="max-width: 20px; margin-bottom: 2px;">
                File Security Manager
            </div>

            {{-- Operation Buttons --}}
            <div class="op-buttons">
                <button id="encryptBtn" class="op-btn-main op-btn-main-active" data-op="encrypt">
                    <i class="fas fa-lock"></i> Encrypt
                </button>
                <button id="decryptBtn" class="op-btn-main" data-op="decrypt">
                    <i class="fas fa-lock-open"></i> Decrypt
                </button>
            </div>

            {{-- Encrypt Form --}}
            <div id="encryptForm" class="crypto-form-section">
                <form method="POST" action="{{ route('encrypt') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label>Choose Algorithm</label>
                        <select name="algorithm">
                            <option value="AES">AES-128 (Recommended)</option>
                            <option value="DES">DES (3-20 character key)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>5-20 Character Secret Key <span style="color:var(--danger)">*</span></label>
                        <input type="text" name="key" class="key-input"
                               placeholder="Enter a 5-20 character key"
                               maxlength="20" autocomplete="off" required>
                        <div class="key-counter bad">0 / 5-20 characters</div>
                    </div>
                    <div class="form-group">
                        <label>Choose File</label>
                        <div class="file-input-wrapper">
                            <div class="file-drop-zone-sidebar">
                                <i class="fas fa-cloud-arrow-up"></i>
                                <div class="drop-text-sidebar">Drop file here or click to browse</div>
                                <div class="drop-hint-sidebar">Max 50MB - Any file type</div>
                            </div>
                            <input type="file" name="file" class="file-input" required>
                        </div>
                        <a href="#" class="browse-file-link" onclick="event.preventDefault(); this.closest('.file-input-wrapper').querySelector('.file-input').click();">Browse File</a>
                        <div id="encryptSuccessMsg" class="success-message" style="display:none;"><i class="fas fa-check-circle"></i> successful</div>
                        @if($errors->any())
                            <div class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
                        @endif
                    </div>
                    <button type="submit" class="btn btn-primary btn-large">
                        <i class="fas fa-lock"></i> Encrypt File
                    </button>
                </form>
            </div>

            {{-- Decrypt Form --}}
            <div id="decryptForm" class="crypto-form-section" style="display: none;">
                <form method="POST" action="{{ route('decrypt') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label>Choose Algorithm</label>
                        <select name="algorithm">
                            <option value="AES">AES-128</option>
                            <option value="DES">DES</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>5-20 Character Secret Key <span style="color:var(--danger)">*</span></label>
                        <input type="text" name="key" class="key-input"
                               placeholder="Use the same key used for encryption"
                               maxlength="20" autocomplete="off" required>
                        <div class="key-counter bad">0 / 5-20 characters</div>
                    </div>
                    <div class="form-group">
                        <label>Encrypted File (.enc)</label>
                        <div class="file-input-wrapper">
                            <div class="file-drop-zone-sidebar">
                                <i class="fas fa-cloud-arrow-up"></i>
                                <div class="drop-text-sidebar">Drop encrypted file or click to browse</div>
                                <div class="drop-hint-sidebar">Upload an .enc file</div>
                            </div>
                            <input type="file" name="file" class="file-input" required>
                        </div>
                        <a href="#" class="browse-file-link" onclick="event.preventDefault(); this.closest('.file-input-wrapper').querySelector('.file-input').click();">Browse File</a>
                        <div id="decryptSuccessMsg" class="success-message" style="display:none;"><i class="fas fa-check-circle"></i> successful</div>
                        @if($errors->any())
                            <div class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
                        @endif
                    </div>
                    <button type="submit" class="btn btn-primary btn-large">
                        <i class="fas fa-lock-open"></i> Decrypt File
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- RIGHT MAIN CONTENT --}}
    <div class="main-content">
        {{-- Statistics Cards --}}
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">{{ count($files) }}</div>
                <div class="stat-label">Total Files</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ count(array_filter($files->toArray(), fn($f) => $f['type'] === 'encrypted')) }}</div>
                <div class="stat-label">Encrypted</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ count(array_filter($files->toArray(), fn($f) => $f['type'] === 'decrypted')) }}</div>
                <div class="stat-label">Decrypted</div>
            </div>
        </div>

        {{-- File Table --}}
        <div class="table-card">
            <div class="table-header">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-database" style="color: var(--accent); font-size: 1.2rem;"></i>
                    <span class="table-title">Your Files</span>
                    <span class="file-count">{{ count($files) }}</span>
                </div>
                <div id="typeFilterButtons" style="display: flex; gap: 6px; flex-wrap: wrap;">
                    <button class="filter-btn active" data-filter="all">
                        <i class="fas fa-list"></i> All
                    </button>
                    <button class="filter-btn" data-filter="encrypted">
                        <i class="fas fa-lock"></i> Encrypted
                    </button>
                    <button class="filter-btn" data-filter="decrypted">
                        <i class="fas fa-lock-open"></i> Decrypted
                    </button>
                </div>
            </div>

            @if($files->isEmpty())
                <div class="text-center text-muted" style="padding: 60px 20px;">
                    <i class="fas fa-folder-open" style="font-size:3rem; margin-bottom:16px; display:block; opacity:.3"></i>
                    <div style="font-size: 0.95rem;">No files yet. Use the form to get started!</div>
                </div>
            @else
                <div class="table-wrapper">
                    <table id="filesTable" class="files-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>File Name</th>
                                <th>Algorithm</th>
                                <th>Size</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($files as $i => $file)
                            <tr class="file-row" data-type="{{ $file->type }}">
                                <td style="color:var(--muted); font-weight: 600;">{{ $i + 1 }}</td>
                                <td>
                                    <div class="file-name-cell">
                                        <i class="fas fa-file" style="color:var(--accent); margin-right:6px"></i>
                                        {{ $file->original_name }}
                                        <span class="file-type-icon" title="{{ $file->type === 'encrypted' ? 'Encrypted' : 'Decrypted' }}">
                                            @if($file->type === 'encrypted')
                                                <i class="fas fa-lock" style="color: var(--success); margin-left: 8px;"></i>
                                            @else
                                                <i class="fas fa-lock-open" style="color: var(--danger); margin-left: 8px;"></i>
                                            @endif
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $file->algorithm === 'AES' ? 'badge-aes' : 'badge-des' }}">
                                        {{ $file->algorithm }}
                                    </span>
                                </td>
                                <td style="color:var(--muted)">{{ number_format($file->file_size / 1024, 1) }} KB</td>
                                <td style="color:var(--muted)">{{ $file->created_at->format('d M Y') }}</td>
                                <td style="display:flex; gap:6px; flex-wrap:wrap;">
                                    <a href="{{ $file->download_route }}" class="btn btn-outline btn-sm" title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    @if($file->type === 'decrypted')
                                        <a href="{{ $file->open_route }}" target="_blank" class="btn btn-outline btn-sm" title="Open">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endif
                                    @if($file->type === 'encrypted')
                                        <button type="button" class="btn btn-outline btn-sm share-open-btn" title="Share"
                                                data-modal-target="shareModal-{{ $file->id }}">
                                            <i class="fas fa-share-nodes"></i>
                                        </button>
                                    @endif
                                    <form method="POST" action="{{ $file->delete_route }}" class="delete-form"
                                          onsubmit="return confirm('Delete this file?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Shared With You --}}
        <div class="table-card">
            <div class="table-header">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-people-arrows" style="color: var(--accent); font-size: 1.2rem;"></i>
                    <span class="table-title">Shared With You</span>
                    <span class="file-count">{{ count($sharedWithMe) }}</span>
                </div>
            </div>

            @if($sharedWithMe->isEmpty())
                <div class="text-center text-muted" style="padding: 40px 20px;">
                    <i class="fas fa-inbox" style="font-size:2.5rem; margin-bottom:12px; display:block; opacity:.3"></i>
                    <div style="font-size: 0.9rem;">No files have been shared with you yet.</div>
                </div>
            @else
                <div class="table-wrapper">
                    <table class="files-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>File Name</th>
                                <th>Algorithm</th>
                                <th>Shared By</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sharedWithMe as $i => $share)
                            <tr>
                                <td style="color:var(--muted); font-weight: 600;">{{ $i + 1 }}</td>
                                <td>
                                    <div class="file-name-cell">
                                        <i class="fas fa-file" style="color:var(--accent); margin-right:6px"></i>
                                        {{ $share->encryptedFile->original_name }}
                                        <i class="fas fa-lock" style="color: var(--success); margin-left: 8px;" title="Encrypted"></i>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $share->encryptedFile->algorithm === 'AES' ? 'badge-aes' : 'badge-des' }}">
                                        {{ $share->encryptedFile->algorithm }}
                                    </span>
                                </td>
                                <td style="color:var(--muted)">{{ $share->sharedBy->email }}</td>
                                <td style="color:var(--muted)">{{ $share->created_at->format('d M Y') }}</td>
                                <td style="display:flex; gap:6px; flex-wrap:wrap;">
                                    <a href="{{ route('download.encrypted', $share->encryptedFile) }}" class="btn btn-outline btn-sm" title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-muted" style="padding: 12px 4px 0; font-size: 0.8rem;">
                    <i class="fas fa-circle-info"></i> Download the file, then use the Decrypt form with the algorithm and key the sender gives you.
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Share Modals (one per encrypted file) --}}
@foreach($files as $file)
    @if($file->type === 'encrypted')
        <div class="modal-overlay" id="shareModal-{{ $file->id }}">
            <div class="modal-box">
                <div class="modal-header">
                    <div class="modal-title"><i class="fas fa-share-nodes"></i> Share "{{ $file->original_name }}"</div>
                    <button type="button" class="modal-close-btn" data-modal-close="shareModal-{{ $file->id }}">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>

                <form method="POST" action="{{ $file->share_route }}">
                    @csrf
                    <div class="form-group">
                        <label>Recipient's Account Email</label>
                        <input type="email" name="email" placeholder="user@example.com" required autocomplete="off"
                               value="{{ old('_share_file_id') == $file->id ? old('email') : '' }}">
                        @if($errors->share->has('email') && old('_share_file_id') == $file->id)
                            <div class="error-message"><i class="fas fa-exclamation-circle"></i> {{ $errors->share->first('email') }}</div>
                        @endif
                        <input type="hidden" name="_share_file_id" value="{{ $file->id }}">
                    </div>
                    <button type="submit" class="btn btn-primary btn-large" style="width:100%">
                        <i class="fas fa-paper-plane"></i> Share File
                    </button>
                </form>

                @if($file->shares->isNotEmpty())
                    <div class="share-recipients">
                        <div class="share-recipients-title">Already shared with</div>
                        @foreach($file->shares as $share)
                            <div class="share-recipient-row">
                                <span><i class="fas fa-user" style="color:var(--muted); margin-right:6px"></i>{{ $share->sharedWith->email }}</span>
                                <form method="POST" action="{{ route('file.share.revoke', $share) }}"
                                      onsubmit="return confirm('Revoke access for {{ $share->sharedWith->email }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Revoke">
                                        <i class="fas fa-xmark"></i>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif
@endforeach

    <script>
        // Show success message based on session
        @if(session('success') === 'encryption-success')
            const encryptMsg = document.getElementById('encryptSuccessMsg');
            if (encryptMsg) {
                encryptMsg.style.display = 'flex';
                setTimeout(() => {
                    encryptMsg.style.display = 'none';
                }, 5000);
            }
        @endif

        @if(session('success') === 'decryption-success')
            const decryptMsg = document.getElementById('decryptSuccessMsg');
            if (decryptMsg) {
                decryptMsg.style.display = 'flex';
                setTimeout(() => {
                    decryptMsg.style.display = 'none';
                }, 5000);
            }
        @endif

        // Operation buttons - use IDs for new layout
        const encryptBtn = document.getElementById('encryptBtn');
        const decryptBtn = document.getElementById('decryptBtn');
        
        if (encryptBtn) {
            encryptBtn.addEventListener('click', function() {
                this.classList.add('op-btn-main-active');
                decryptBtn.classList.remove('op-btn-main-active');
                document.getElementById('encryptForm').style.display = 'block';
                document.getElementById('decryptForm').style.display = 'none';
            });
        }
        
        if (decryptBtn) {
            decryptBtn.addEventListener('click', function() {
                this.classList.add('op-btn-main-active');
                encryptBtn.classList.remove('op-btn-main-active');
                document.getElementById('decryptForm').style.display = 'block';
                document.getElementById('encryptForm').style.display = 'none';
            });
        }

        // File type filter
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const filterType = this.getAttribute('data-filter');
                
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                document.querySelectorAll('.file-row').forEach(row => {
                    const rowType = row.getAttribute('data-type');
                    if (filterType === 'all' || rowType === filterType) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });

        // File drop zones - sidebar version with improved handling
        document.querySelectorAll('.file-drop-zone-sidebar').forEach(zone => {
            const input = zone.closest('.file-input-wrapper').querySelector('.file-input');
            
            // Click to select file
            zone.addEventListener('click', () => input.click());
            
            // Drag over effect
            zone.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.stopPropagation();
                zone.style.background = 'rgba(59, 130, 246, 0.2)';
                zone.style.borderColor = '#3B82F6';
                zone.style.transform = 'scale(1.01)';
            });
            
            // Drag leave effect
            zone.addEventListener('dragleave', (e) => {
                e.preventDefault();
                e.stopPropagation();
                zone.style.background = '';
                zone.style.borderColor = '';
                zone.style.transform = '';
            });
            
            // Drop file
            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                e.stopPropagation();
                zone.style.background = '';
                zone.style.borderColor = '';
                zone.style.transform = '';
                
                const files = e.dataTransfer.files;
                if (files && files.length > 0) {
                    input.files = files;
                    // Show file name in UI
                    const fileName = files[0].name;
                    const textElement = zone.querySelector('.drop-text-sidebar');
                    if (textElement) {
                        textElement.innerText = '✓ ' + fileName;
                        textElement.style.color = '#22c55e';
                    }
                }
            });
            
            // Update filename when file is selected via browse
            input.addEventListener('change', () => {
                if (input.files && input.files.length > 0) {
                    const fileName = input.files[0].name;
                    const textElement = zone.querySelector('.drop-text-sidebar');
                    if (textElement) {
                        textElement.innerText = '✓ ' + fileName;
                        textElement.style.color = '#22c55e';
                    }
                }
            });
        });
        
        // Prevent default browser behavior for entire document
        document.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.stopPropagation();
        });
        
        document.addEventListener('drop', (e) => {
            e.preventDefault();
            e.stopPropagation();
        });

        // Share modal open/close
        function openShareModal(id) {
            const modal = document.getElementById(id);
            if (modal) modal.classList.add('modal-open');
        }
        function closeShareModal(id) {
            const modal = document.getElementById(id);
            if (modal) modal.classList.remove('modal-open');
        }

        document.querySelectorAll('.share-open-btn').forEach(btn => {
            btn.addEventListener('click', () => openShareModal(btn.getAttribute('data-modal-target')));
        });

        document.querySelectorAll('.modal-close-btn').forEach(btn => {
            btn.addEventListener('click', () => closeShareModal(btn.getAttribute('data-modal-close')));
        });

        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) overlay.classList.remove('modal-open');
            });
        });

        @if(session('success') === 'share-success')
            (function() {
                const toast = document.createElement('div');
                toast.className = 'success-message share-toast';
                toast.innerHTML = '<i class="fas fa-check-circle"></i> File shared successfully.';
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 5000);
            })();
        @endif

        @if($errors->share->any())
            (function() {
                const toast = document.createElement('div');
                toast.className = 'error-message share-toast';
                toast.innerHTML = '<i class="fas fa-exclamation-circle"></i> {{ $errors->share->first('email') }}';
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 5000);
            })();
        @endif

        @if($errors->share->any())
            openShareModal('shareModal-{{ old('_share_file_id') }}');
        @endif
    </script>

</div>
@endsection