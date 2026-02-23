@extends('hyro::admin.layouts.app')

@section('header', 'Roles Management')

@section('actions')
    @can('create-roles')
        <a href="{{ route('hyro.admin.roles.create') }}"
           class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
            Create Role
        </a>
    @endcan
@endsection

@section('content')
    <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Role
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Slug
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Users
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Privileges
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($roles as $role)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $role->name }}</div>
                                @if($role->description)
                                    <div class="text-sm text-gray-500">{{ Str::limit($role->description, 50) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $role->slug }}</code>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $role->users_count }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $role->privileges_count }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    @can('edit-roles')
                                        <a href="{{ route('hyro.admin.roles.edit', $role) }}"
                                           class="text-indigo-600 hover:text-indigo-900">
                                            Edit
                                        </a>
                                        <a href="{{ route('hyro.admin.roles.privileges.edit', $role) }}"
                                           class="text-indigo-600 hover:text-indigo-900">
                                            Privileges
                                        </a>
                                    @endcan

                                    @can('delete-roles')
                                        @if($role->slug !== config('hyro.super_admin_role', 'super-admin'))
                                            <form action="{{ route('hyro.admin.roles.destroy', $role) }}"
                                                  method="POST"
                                                  class="inline"
                                                  data-confirm="Are you sure you want to delete this role?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="text-red-600 hover:text-red-900">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $roles->links() }}
            </div>
        </div>
    </div>
@endsection
