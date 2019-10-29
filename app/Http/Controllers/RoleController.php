<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{


  
    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function index(Request $request)
    {
        $response = Role::all();
        foreach($response as $entity){
            $role = clone $entity;
            $permissions = $role->permissions;
            $permissionIds = array();
            foreach($permissions as $permission){
                $permissionIds[] = $permission->id;
            }
            $entity['permissions'] = $permissionIds;
        }
        return response()->json($response);
    }
    public function update(Request $request)
    {
        $role = Role::find($request->input('id'));
        $role->name = $request->input('name');
        $role->save();
        $requestPermissions = $request->input('permissions');
        $permissionIds = array();
        foreach($role->permissions as $permission){
            $permissionIds[] = $permission->id;
        }
        $addPermissions = array_diff($requestPermissions, $permissionIds);
        $deletePermissions = array_diff($permissionIds, $requestPermissions);
        foreach($addPermissions as $id){
            $permission = Permission::find($id);
            $role->givePermissionTo($permission);
            $permission->assignRole($role);
        }
        foreach($deletePermissions as $id){
            $permission = Permission::find($id);
            $role->revokePermissionTo($permission);
            $permission->removeRole($role);
        }
        return response()->json($role);
    }
    public function findRoles(Request $request)
    {
        $page_size = $request->input('pageSize');
        $response = Role::paginate($page_size);
        foreach($response->items() as $entity){
            $role = clone $entity;
            $permissions = $role->permissions;
            $permissionIds = array();
            foreach($permissions as $permission){
                $permissionIds[] = $permission->id;
            }
            $entity['permissions'] = $permissionIds;
        }
        return response()->json($response);
    }

    
}