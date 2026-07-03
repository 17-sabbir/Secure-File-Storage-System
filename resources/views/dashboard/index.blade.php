@extends('layouts.app')
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
                                    <a href="{{ $file->download_route }}" class="btn btn-outline btn-sm">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <form method="POST" action="{{ $file->delete_route }}" class="delete-form"
                                          onsubmit="return confirm('Delete this file?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">
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
    </div>
</div>

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
    </script>

</div>
@endsection