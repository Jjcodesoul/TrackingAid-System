<x-app-layout>
    {{-- Inline styling overrides for standard dashboard metrics --}}
    <style>
        .custom-card {
            background: #ffffff !important;
            border-radius: 8px !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05) !important;
        }
        .btn-green {
            background-color: #22c55e !important;
            color: white !important;
            font-weight: 500 !important;
            padding: 7px 14px !important;
            border-radius: 6px !important;
            border: none !important;
            font-size: 13px !important;
            transition: all 0.15s ease !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            cursor: pointer !important;
            text-decoration: none !important;
        }
        .btn-green:hover { background-color: #16a34a !important; }
        
        .role-badge-staff {
            background-color: #f0fdf4 !important;
            color: #16a34a !important;
            font-weight: 600 !important;
            font-size: 11px !important;
            padding: 2px 8px !important;
            border-radius: 4px !important;
            text-transform: uppercase !important;
            display: inline-block !important;
            border: 1px solid #bbf7d0 !important;
        }
        .role-badge-user {
            background-color: #f0f9ff !important;
            color: #0284c7 !important;
            font-weight: 600 !important;
            font-size: 11px !important;
            padding: 2px 8px !important;
            border-radius: 4px !important;
            text-transform: uppercase !important;
            display: inline-block !important;
            border: 1px solid #bae6fd !important;
        }

        .text-link-edit { color: #64748b !important; font-weight: 500 !important; font-size: 13px !important; text-decoration: none !important; cursor: pointer !important; }
        .text-link-edit:hover { color: #1e293b !important; text-decoration: underline !important; }
        .text-link-disable { color: #ef4444 !important; font-weight: 500 !important; font-size: 13px !important; background: none !important; border: none !important; cursor: pointer !important; padding: 0 !important; }
        .text-link-disable:hover { color: #dc2626 !important; text-decoration: underline !important; }
        
        /* Form Inputs */
        .form-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 14px;
            margin-top: 4px;
            box-sizing: border-box;
        }
        .form-input:focus {
            border-color: #22c55e;
            outline: none;
            box-shadow: 0 0 0 1px #22c55e;
        }
    </style>

    {{-- Fallback: If URL contains ?open=true, show modal, otherwise hide it --}}
    @php $showModal = request()->get('open') === 'true'; @endphp

    <div style="background-color: #f8fafc; min-height: 100vh; width: 100%; padding: 20px 0;">
        <div style="max-width: 1140px; margin: 0 auto; padding: 0 16px;">
            
            {{-- Validation / Success Feedback Alerts --}}
            @if(session('success'))
                <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; color: #16a34a; padding: 12px; border-radius: 6px; margin-bottom: 16px; font-size: 14px; text-align: left;">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div style="background-color: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 12px; border-radius: 6px; margin-bottom: 16px; font-size: 14px; text-align: left;">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            {{-- Header Section --}}
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 18px;">
                <div style="text-align: left;">
                    <h2 style="font-size: 20px; font-weight: 600; color: #0f172a; margin: 0; letter-spacing: -0.01em;">Users & Roles</h2>
                    <p style="font-size: 13px; color: #64748b; margin: 2px 0 0 0;">Manage user accounts and permissions</p>
                </div>
                
                {{-- FIXED: This button now triggers a clean URL redirect to open the view safely --}}
                <a href="/users?open=true" class="btn-green">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:14px; height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Add New User
                </a>
            </div>

            {{-- Users Data Table Card --}}
            <div class="custom-card" style="overflow: hidden;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 1px solid #e2e8f0; background-color: #f8fafc;">
                            <th style="padding: 10px 14px; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; width: 30%;">Name</th>
                            <th style="padding: 10px 14px; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; width: 40%;">Email</th>
                            <th style="padding: 10px 14px; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; width: 15%; text-align: center;">Role</th>
                            <th style="padding: 10px 14px; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; width: 15%; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody style="background-color: #ffffff;">
                        @forelse($users as $user)
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 12px 14px; font-size: 13px; font-weight: 500; color: #1e293b;">
                                    {{ $user->name }}
                                </td>
                                <td style="padding: 12px 14px; font-size: 13px; color: #475569;">
                                    {{ $user->email }}
                                </td>
                                <td style="padding: 12px 14px; text-align: center; vertical-align: middle;">
                                    <span class="{{ $user->role === 'staff' ? 'role-badge-staff' : 'role-badge-user' }}">
                                        {{ $user->role ?? 'User' }}
                                    </span>
                                </td>
                                <td style="padding: 12px 14px; text-align: right; vertical-align: middle;">
                                    <div style="display: flex; justify-content: flex-end; align-items: center; gap: 12px;">
                                        <a class="text-link-edit">Edit</a>
                                        <form action="/users/{{ $user->id }}" method="POST" onsubmit="return confirm('Disable this user account?');" style="margin: 0; display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-link-disable">Disable</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="padding: 32px; text-align: center; color: #94a3b8; font-size: 13px;">
                                    No records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

        {{-- ==================== POPUP MODAL COMPONENT ==================== --}}
        @if($showModal)
        <div style="position: fixed; inset: 0; display: flex; align-items: center; justify-content: center; z-index: 9999;">
            
            {{-- FIXED: Clicking the backdrop executes a clean routing redirect --}}
            <a href="/users" style="position: absolute; inset: 0; background-color: rgba(15, 23, 42, 0.4); z-index: 40; cursor: default;"></a>
            
            {{-- Form Content Box --}}
            <div style="background: #ffffff; width: 100%; max-width: 450px; border-radius: 8px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); padding: 20px; z-index: 50; position: relative;">
                
                {{-- Modal Header --}}
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                    <h3 style="font-size: 16px; font-weight: 600; color: #0f172a; margin: 0;">Create New User Account</h3>
                    {{-- FIXED: Changed into an anchor link. This is 100% clickable and will exit immediately --}}
                    <a href="/users" style="background: none; border: none; font-size: 24px; color: #94a3b8; cursor: pointer; line-height: 1; padding: 0 4px; text-decoration: none;">&times;</a>
                </div>

                {{-- Creation Form --}}
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    
                    <div style="margin-bottom: 12px; text-align: left;">
                        <label style="font-size: 12px; font-weight: 600; color: #475569; display: block;">Full Name</label>
                        <input type="text" name="name" required class="form-input" placeholder="John Doe">
                    </div>

                    <div style="margin-bottom: 12px; text-align: left;">
                        <label style="font-size: 12px; font-weight: 600; color: #475569; display: block;">Email Address</label>
                        <input type="email" name="email" required class="form-input" placeholder="johndoe@example.com">
                    </div>

                    <div style="margin-bottom: 12px; text-align: left;">
                        <label style="font-size: 12px; font-weight: 600; color: #475569; display: block;">Password</label>
                        <input type="password" name="password" required class="form-input" placeholder="••••••••">
                    </div>

                    <div style="margin-bottom: 20px; text-align: left;">
                        <label style="font-size: 12px; font-weight: 600; color: #475569; display: block;">Assigned System Role</label>
                        <select name="role" class="form-input" style="height: 38px; background-color: white;">
                            <option value="user">User</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>

                    {{-- Form Footer Actions --}}
                    <div style="display: flex; justify-content: flex-end; gap: 8px; border-top: 1px solid #e2e8f0; padding-top: 12px;">
                        {{-- FIXED: Changed from type="button" to a direct HTML anchor redirection --}}
                        <a href="/users" style="background: #f1f5f9; color: #475569; border: none; padding: 7px 14px; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; text-decoration: none; display: inline-block;">Cancel</a>
                        <button type="submit" class="btn-green">Save User Record</button>
                    </div>
                </form>
            </div>
        </div>
        @endif
        {{-- ==================== END OF MODAL ==================== --}}

    </div>
</x-app-layout>