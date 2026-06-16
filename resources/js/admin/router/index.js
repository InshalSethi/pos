import { createRouter, createWebHistory } from 'vue-router';
import Layout from '../views/Layout.vue';
import Login from '../views/Login.vue';
import Dashboard from '../views/Dashboard.vue';
import AdminsIndex from '../views/admins/Index.vue';
import AdminsForm from '../views/admins/Form.vue';
import UsersIndex from '../views/users/Index.vue';
import UsersForm from '../views/users/Form.vue';
import RolesIndex from '../views/roles/Index.vue';
import RolesForm from '../views/roles/Form.vue';
import ProfileIndex from '../views/profile/Index.vue';
import SettingsIndex from '../views/settings/Index.vue';

const routes = [
    {
        path: '/admin/login',
        name: 'admin.login',
        component: Login,
        meta: { guest: true }
    },
    {
        path: '/admin',
        component: Layout,
        meta: { requiresAuth: true },
        children: [
            { path: '', name: 'admin.dashboard', component: Dashboard },
            
            { path: 'admins', name: 'admin.admins.index', component: AdminsIndex },
            { path: 'admins/create', name: 'admin.admins.create', component: AdminsForm },
            { path: 'admins/:id/edit', name: 'admin.admins.edit', component: AdminsForm },
            
            { path: 'users', name: 'admin.users.index', component: UsersIndex },
            { path: 'users/create', name: 'admin.users.create', component: UsersForm },
            { path: 'users/:id/edit', name: 'admin.users.edit', component: UsersForm },
            
            { path: 'roles', name: 'admin.roles.index', component: RolesIndex },
            { path: 'roles/create', name: 'admin.roles.create', component: RolesForm },
            { path: 'roles/:id/edit', name: 'admin.roles.edit', component: RolesForm },
            
            { path: 'profile', name: 'admin.profile', component: ProfileIndex },
            { path: 'settings', name: 'admin.settings', component: SettingsIndex },
        ]
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

router.beforeEach((to, from, next) => {
    const isAuthenticated = localStorage.getItem('admin_token') || sessionStorage.getItem('admin_logged_in');
    if (to.meta.requiresAuth && !isAuthenticated) {
        next({ name: 'admin.login' });
    } else if (to.meta.guest && isAuthenticated) {
        next({ name: 'admin.dashboard' });
    } else {
        next();
    }
});

export default router;
