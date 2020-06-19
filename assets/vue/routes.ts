import {RouteConfig} from 'vue-router';

const routes: RouteConfig[] = [{
    name: 'dashboard',
    path: '/dashboard',
    component: () => import(/* webpackChunkName: "dashboard" */ './pages/dashboard/DashboardPage.vue'),
    meta: {
      title: 'title.dashboard',
    },
  }, {
    path: '*',
    redirect: {name: 'dashboard'},
  },
];

export default routes;
