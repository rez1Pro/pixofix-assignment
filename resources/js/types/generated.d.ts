declare namespace App.Data {
export type FileItemData = {
id: number;
order_id: number;
filename: string;
original_filename: string;
filepath: string;
directory_path: string | null;
file_type: string;
file_size: number;
status: string;
is_processed: boolean;
created_at: string;
updated_at: string;
};
export type OrderData = {
id: number;
order_number: string;
name: string;
description: string | null;
created_by: number;
status: string;
completed_at: string | null;
approved_at: string | null;
created_at: string;
updated_at: string;
creator: App.Data.UserData;
fileItems: Array<App.Data.FileItemData> | null;
stats: Array<any> | null;
};
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
export type OrderManagementPermissions = 'view:orders' | 'create:orders' | 'edit:orders' | 'delete:orders' | 'view:files' | 'create:files' | 'edit:files' | 'delete:files' | 'view:claims';
export type RolePermissions = 'role:view' | 'role:create' | 'role:update' | 'role:delete';
export type UserPermissions = 'user:view' | 'user:create' | 'user:update' | 'user:delete';
}
