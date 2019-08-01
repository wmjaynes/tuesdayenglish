<template>

    <div>
        <ul class="list-group">
            <li class="list-group-item" v-for="caller in dance.callers">
                {{ (new Date(caller.pivot.date_of)).toLocaleDateString("en-US", date_options) }}
                :  {{caller.name}}
            </li>
        </ul>
    </div>

</template>

<script>
    export default {
        name: "dance-history",
        props: ['dance'],

        data() {
            return {
                date_options: {weekday: 'short', year: 'numeric', month: 'short', day: 'numeric'},
            }
        },
    }
</script>

<style scoped>

</style>
