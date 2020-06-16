<template>
    <div>
        <div class="loader">
            <font-awesome-icon icon="circle-notch" spin size="8x"/>
            <div class="loader-text">{{ 'brand.loading'|trans }}</div>
        </div>
    </div>
</template>

<script lang="ts">
import {Component, Vue} from 'vue-property-decorator';

@Component
export default class App extends Vue {

}
</script>

<style lang="scss" scoped>

</style>
