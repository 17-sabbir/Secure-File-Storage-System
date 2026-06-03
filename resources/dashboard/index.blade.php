@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="container">

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger"><i class="fas fa-triangle-exclamation"></i> {{ $errors->first() }}</div>
    @endif

    {{-- ── Encrypt & Decrypt Panels ── --}}
    <div class="grid-2">

        {{-- Encrypt --}}
        <div class="card">
            <div class="card-title"><i class="fas fa-lock"></i> Encrypt File</div>
            <form method="POST" action="{{ route('encrypt') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label>Choose Algorithm</label>
                    <select name="algorithm">
                        <option value="AES">AES-128 (Recommended)</option>
                        <option value="DES">DES (8-character key)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>16-Character Secret Key <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="key" class="key-input"
                           placeholder="Enter a 16-character key"
                           maxlength="16" autocomplete="off" required>
                    <div class="key-counter bad">0 / 16 characters</div>
                </div>
                <div class="form-group">
                    <label>Choose File</label>
                    <input type="file" name="file" required>
                    <div class="text-muted" style="margin-top:5px">Max: 50MB | Any file type</div>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">
                    <i class="fas fa-lock"></i> Encrypt
                </button>
            </form>
        </div>

        {{-- Decrypt --}}
        <div class="card">
            <div class="card-title"><i class="fas fa-lock-open"></i> Decrypt File</div>
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
                    <label>16-Character Secret Key <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="key" class="key-input"
                           placeholder="Use the same key used for encryption"
                           maxlength="16" autocomplete="off" required>
                    <div class="key-counter bad">0 / 16 characters</div>
                </div>
                <div class="form-group">
                    <label>Encrypted File (.enc)</label>
                    <input type="file" name="file" required>
                    <div class="text-muted" style="margin-top:5px">Upload an encrypted file</div>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">
                    <i class="fas fa-lock-open"></i> Decrypt
                </button>
            </form>
        </div>

    </div>{{-- .grid-2 --}}

    {{-- ── File Storage Table ── --}}
    <div class="card mt-24">
        <div class="card-title"><i class="fas fa-database"></i> Your Files</div>

        @if($files->isEmpty())
            <div class="text-center text-muted" style="padding: 40px 0;">
                <i class="fas fa-folder-open" style="font-size:2.5rem; margin-bottom:12px; display:block; opacity:.4"></i>
                No files yet. Encrypt a file above to get started.
            </div>
        @else
            <div style="overflow-x:auto">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>File Name</th>
                            <th>Algorithm</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($files as $i => $file)
                        <tr>
                            <td style="color:var(--muted)">{{ $i + 1 }}</td>
                            <td>
                                <i class="fas fa-file" style="color:var(--accent); margin-right:6px"></i>
                                {{ $file->original_name }}
                            </td>
                            <td>
                                <span class="badge {{ $file->algorithm === 'AES' ? 'badge-aes' : 'badge-des' }}">
                                    {{ $file->algorithm }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $file->type === 'encrypted' ? 'badge-enc' : 'badge-dec' }}">
                                    {{ $file->type === 'encrypted' ? '🔒 Encrypted' : '🔓 Decrypted' }}
                                </span>
                            </td>
                            <td style="color:var(--muted)">{{ number_format($file->file_size / 1024, 1) }} KB</td>
                            <td style="color:var(--muted)">{{ $file->created_at->format('d M Y, H:i') }}</td>
                            <td style="display:flex; gap:6px; flex-wrap:wrap;">
                                <a href="{{ $file->download_route }}" class="btn btn-outline btn-sm">
                                    <i class="fas fa-download"></i> Download
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
@endsection