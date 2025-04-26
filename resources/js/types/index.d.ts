import { Config } from 'ziggy-js';

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: App.Data.UserData;
        permissions: App.Enums.Permissions[keyof typeof App.Enums.Permissions][];
    };
    ziggy: Config & { location: string };
};
