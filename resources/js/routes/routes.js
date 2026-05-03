import { authStore } from "../store/auth";

const AuthenticatedLayout = () => import('../layouts/AdminLayout.vue');
const AuthenticatedUserLayout = () => import('../layouts/UserLayout.vue');
const GuestLayout = () => import('../layouts/GuestLayout.vue');

async function requireLogin(to, from, next) {
    const auth = authStore();
    const isLogin = !!auth.authenticated;

    if (isLogin) {
        next()
    } else {
        next('/login')
    }
}

const hasAdmin = (roles = []) =>
    roles.some((role) => role?.name?.toLowerCase().includes('admin'));

async function guest(to, from, next) {
    const auth = authStore()
    let isLogin = !!auth.authenticated;

    if (isLogin) {
        next('/')
    } else {
        next()
    }
}

async function requireAdmin(to, from, next) {
    const auth = authStore();
    let isLogin = !!auth.authenticated;
    let user = auth.user;

    if (isLogin) {
        if (hasAdmin(user.roles)) {
            next()
        } else {
            next('/app')
        }
    } else {
        next('/login')
    }
}

export default [
    {
        path: '/',
        component: GuestLayout,
        children: [
            {
                path: '/',
                name: 'public.home',
                component: () => import('../views/public/home/index.vue'),
            },
            {
                path: 'search',
                name: 'public.search',
                component: () => import('../views/public/search/Results.vue'),
            },
            {
                path: 'brands',
                name: 'public.brands',
                component: () => import('../views/public/brands/Index.vue'),
            },
            {
                path: 'explore',
                name: 'public.explore',
                component: () => import('../views/public/search/Results.vue'),
            },
            {
                path: 'u/:id',
                name: 'public.profile',
                component: () => import('../views/public/profile/Show.vue'),
            },

            {
                path: 'login',
                name: 'auth.login',
                component: () => import('../views/auth/login/Login.vue'),
                beforeEnter: guest,
            },
            {
                path: 'register',
                name: 'auth.register',
                component: () => import('../views/auth/register/index.vue'),
                beforeEnter: guest,
            },
            {
                path: 'forgot-password',
                name: 'auth.forgot-password',
                component: () => import('../views/auth/passwords/Email.vue'),
                beforeEnter: guest,
            },
            {
                path: 'reset-password/:token',
                name: 'auth.reset-password',
                component: () => import('../views/auth/passwords/Reset.vue'),
                beforeEnter: guest,
            },
            {
                path: 'product/:id',
                name: 'public.product.show',
                component: () => import('../views/public/product/Show.vue'),
            },
            {
                path: 'studio',
                name: 'public.studio',
                component: () => import('../views/public/studio/Index.vue'),
            },
            {
                path: 'outfit/:id',
                name: 'public.outfit.show',
                component: () => import('../views/public/outfits/Show.vue'),
            },
        ]
    },

    {
        path: '/app',
        component: AuthenticatedUserLayout,
        name: 'app',
        beforeEnter: requireLogin,
        meta: { breadCrumb: '.' },
        children: [
            {
                name: 'app.profile',
                path: 'profile',
                component: () => import('../views/user/profile.vue'),
                meta: {
                    breadCrumb: 'Profile',
                },
            },
            {
                name: 'app.outfits',
                path: 'outfits',
                component: () => import('../views/user/outfits.vue'),
                meta: {
                    breadCrumb: 'My Outfits',
                },
            },

        ]
    },


    {
        path: '/admin',
        component: AuthenticatedLayout,
        beforeEnter: requireAdmin,
        meta: { breadCrumb: 'Dashboard' },
        children: [
            {
                name: 'admin.index',
                path: '',
                component: () => import('../views/admin/index.vue'),
                meta: {
                    breadCrumb: 'Admin',
                    hideBreadcrumb: true
                }
            },
            {
                name: 'profile.index',
                path: 'profile',
                component: () => import('../views/admin/profile/index.vue'),
                meta: { breadCrumb: 'Profile' }
            },

            {
                name: 'products',
                path: 'products',
                meta: { breadCrumb: 'Products' },
                children: [
                    {
                        name: 'products.index',
                        path: '',
                        component: () => import('../views/admin/products/Index.vue'),
                        meta: {
                            breadCrumb: 'View Products',
                            hideBreadcrumb: true
                        }
                    },
                ]
            },
            {
                name: 'categories',
                path: 'categories',
                meta: { breadCrumb: 'Categories' },
                children: [
                    {
                        name: 'categories.index',
                        path: '',
                        component: () => import('../views/admin/categories/Index.vue'),
                        meta: {
                            breadCrumb: 'View Categories',
                            hideBreadcrumb: true
                        }
                    },
                ]
            },
            {
                name: 'sources',
                path: 'sources',
                meta: { breadCrumb: 'Marketplaces' },
                children: [
                    {
                        name: 'sources.index',
                        path: '',
                        component: () => import('../views/admin/sources/Index.vue'),
                        meta: {
                            breadCrumb: 'View Marketplaces',
                            hideBreadcrumb: true
                        }
                    },
                ]
            },
            {
                name: 'brands',
                path: 'brands',
                meta: { breadCrumb: 'Brands' },
                children: [
                    {
                        name: 'brands.index',
                        path: '',
                        component: () => import('../views/admin/brands/Index.vue'),
                        meta: {
                            breadCrumb: 'View Brands',
                            hideBreadcrumb: true
                        }
                    },
                ]
            },
            {
                name: 'outfits',
                path: 'outfits',
                meta: { breadCrumb: 'Outfits' },
                children: [
                    {
                        name: 'outfits.index',
                        path: '',
                        component: () => import('../views/admin/outfits/Index.vue'),
                        meta: {
                            breadCrumb: 'Outfit Moderation',
                            hideBreadcrumb: true
                        }
                    },
                ]
            },

            {
                name: 'permissions',
                path: 'permissions',
                meta: { breadCrumb: 'Permissions' },
                children: [
                    {
                        name: 'permissions.index',
                        path: '',
                        component: () => import('../views/admin/permissions/Index.vue'),
                        meta: {
                            breadCrumb: 'Permissions',
                            hideBreadcrumb: true
                        }
                    },
                ]
            },
            {
                name: 'users',
                path: 'users',
                meta: { breadCrumb: 'Users' },
                children: [
                    {
                        name: 'users.index',
                        path: '',
                        component: () => import('../views/admin/users/Index.vue'),
                        meta: {
                            breadCrumb: 'Users',
                            hideBreadcrumb: true // Hide layout breadcrumb because the Card has its own header
                        }
                    },
                    {
                        name: 'users.create',
                        path: 'create',
                        component: () => import('../views/admin/users/Create.vue'),
                        meta: {
                            breadCrumb: 'Create User',
                            linked: false
                        }
                    },
                    {
                        name: 'users.edit',
                        path: 'edit/:id',
                        component: () => import('../views/admin/users/Edit.vue'),
                        meta: {
                            breadCrumb: 'Edit User',
                            linked: false
                        }
                    }
                ]
            },

            {
                name: 'roles',
                path: 'roles',
                meta: { breadCrumb: 'Roles' },
                children: [
                    {
                        name: 'roles.index',
                        path: '',
                        component: () => import('../views/admin/roles/Index.vue'),
                        meta: {
                            breadCrumb: 'Roles',
                            hideBreadcrumb: true
                        }
                    },
                    {
                        name: 'admin.roles.edit',
                        path: 'edit/:id',
                        component: () => import('../views/admin/roles/Edit.vue'),
                        meta: {
                            breadCrumb: 'Edit Role',
                            linked: false
                        }
                    }
                ]
            },
        ]
    },
    {
        path: "/:pathMatch(.*)*",
        name: 'NotFound',
        component: () => import("../views/errors/404.vue"),
    },
];
