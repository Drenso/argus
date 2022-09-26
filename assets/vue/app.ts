// Await/async imports
import 'core-js/stable';
import 'regenerator-runtime/runtime';

// Global functions
import './functions';

// Vue
import Vue from 'vue';

// Bootstrap
import BootstrapVue from 'bootstrap-vue';

Vue.use(BootstrapVue);

// Font Awesome
import {FontAwesomeIcon, FontAwesomeLayers} from '@fortawesome/vue-fontawesome';

Vue.component('FontAwesomeIcon', FontAwesomeIcon);
Vue.component('FontAwesomeLayers', FontAwesomeLayers);

// Sentry
import * as Sentry from '@sentry/vue';

// Only bind when production mode is set
if (window.SENTRY_DSN) {
  console.log('Setting up Sentry client');

  Sentry.init({
    Vue,
    dsn: window.SENTRY_DSN,
    release: window.SENTRY_RELEASE,
    attachProps: true,
    logErrors: true
  });
}

// Drenso shared
import {Plugins} from '@drenso/vue-frontend-shared';

// Translations
// @ts-ignore Cannot be found, but it is there and working
import I18n from '@trans/messages+intl-icu.en.yml';
// @ts-ignore Cannot be found, but it is there and working
import I18nFrontend from '@trans/frontend+intl-icu.en.yml';
// @ts-ignore Cannot be found, but it is there and working
import I18nValidators from '@trans/validators+intl-icu.en.yml';
// @ts-ignore Cannot be found, but it is there and working
import I18nDrenso from '@drensoTrans/drenso_shared+intl-icu.en.yml';

I18n.frontend = I18nFrontend;
I18n._validation = I18nValidators;
I18n._drenso = I18nDrenso;

// Routing
// @ts-ignore Cannot be found, but it is there and working
import Router from '@fos/router.min.js';
// @ts-ignore Cannot be found, but it is there and working
import fosRoutes from '@/_fos_routes.json';

Vue.use(Plugins.Auth);
Vue.use(Plugins.Modal);
Vue.use(Plugins.Moment, {
  default_tz: 'Europe/Amsterdam',
});
Vue.use(Plugins.Http, {
  loginRoute: 'index',
  testRoute: 'auth_test',
});
Vue.use(Plugins.Random);
Vue.use(Plugins.Router, {
  router: Router,
  routes: fosRoutes,
});
Vue.use(Plugins.Text);
Vue.use(Plugins.Translator, {
  messages: I18n
});
Vue.use(Plugins.Validation);

// Prepare routing
import VueRouter from 'vue-router';
import routes from './routes';

Vue.use(VueRouter);
const router = new VueRouter({routes});
router.beforeEach((to, from, next) => {
  const metaTitle = to.meta ? to.meta.title : undefined;
  document.title = Vue.prototype.$translator.trans(metaTitle && metaTitle !== '' ? metaTitle : 'menu.home')
      + ' | ' + Vue.prototype.$translator.trans('brand.argus');

  next();
});

// Prepare VueX store
import store from './store';

import App from './App.vue';

// Create Vue instance
new Vue({
  render: (h) => h(App),
  router,
  store: store.original,
}).$mount('#app');
