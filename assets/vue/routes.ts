import {RouteConfig} from 'vue-router';
import DashboardPage from './pages/DashboardPage.vue';
import LoginPage from './pages/LoginPage.vue';

const routes: RouteConfig[] = [
  {
    name: 'login',
    path: '/login',
    component: LoginPage,
    meta: {
      title: 'title.login',
    },
  }, {
    name: 'dashboard',
    path: '/dashboard',
    component: DashboardPage,
    meta: {
      title: 'title.dashboard',
    },
  }, {
    path: '*',
    redirect: {name: 'dashboard'},
  },
];

export default routes;
