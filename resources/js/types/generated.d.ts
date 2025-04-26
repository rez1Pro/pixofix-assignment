declare namespace App.Data {
export type RoleData = {
id: number;
name: string;
description: string;
permissions_count: number | null;
users_count: number | null;
created_at: string;
permissions: Array<any> | null;
};
export type UserData = {
id: number;
name: string;
email: string;
phone: string | null;
email_verified_at: string | null;
role: App.Data.RoleData;
};
}
declare namespace App.Enums {
export type RoleEnums = 'Admin' | 'User';
}
declare namespace App.Enums.Permissions {
export type ExamplePermissions = 'view:example';
export type DomainPermission = {
name: string;
value: string;
};
export type RolePermissions = 'role:view' | 'role:create' | 'role:update' | 'role:delete';
export type SettingPermissions = 'setting:view' | 'setting:update';
export type UserPermissions = 'user:view' | 'user:create' | 'user:update';
}
