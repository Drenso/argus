<template>
  <b-navbar
      sticky
      type="dark"
      variant="primary">
    <b-navbar-brand :to="{name: 'dashboard'}">
      <img
          :alt="'brand.argus'|uppercase"
          class="d-inline-block align-top mr-2"
          height="30"
          src="../../../img/argus_white.svg"
          width="30">
      {{ 'brand.argus'|trans|uppercase }}
    </b-navbar-brand>

    <b-navbar-nav class="ml-auto">
      <b-button
          :disabled="logoutPending"
          variant="light"
          @click="logout">
        <font-awesome-icon
            fixed-width
            :icon="logoutPending ? 'circle-notch' : 'sign-out-alt'"
            :spin="logoutPending"/>
        {{ 'auth.button.logout'|trans }}
      </b-button>
    </b-navbar-nav>
  </b-navbar>
</template>

<script lang="ts">
  import {Component, Vue} from 'vue-property-decorator';

  @Component
  export default class Menu extends Vue {
    protected logoutPending = false;

    public async logout(): Promise<void> {
      this.logoutPending = true;
      this.$store.direct.commit.startWork();
      await this.$http.delete(this.$sfRouter.generate('auth_clear'));
      this.$store.direct.commit.loggedOut();
      this.$store.direct.commit.endWork();
    }
  }
</script>
