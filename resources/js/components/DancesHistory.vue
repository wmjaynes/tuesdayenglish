<template>

    <div>
        <form class="form-inline">
            <input class="form-control" size="40" placeholder="Quick search on dance name" type="text" v-model="quickSearchQuery">
            <button @click.prevent="clearQuickSearch();" class="btn btn-secondary">Clear</button>
        </form>

        <ul class="list-group">
            <li class="list-group-item list-group-item-dark" v-for="dance in filteredRecords">
                <span class="font-weight-bold">{{dance.name}}</span>
                <ul class="list-group">
                    <li class="list-group-item" v-for="caller in dance.callers">
                        {{ (new Date(caller.pivot.date_of)).toLocaleDateString("en-US", date_options) }} :
                        {{caller.name}}
                    </li>
                </ul>
            </li>
        </ul>

    </div>

</template>

<script>
    export default {
        // name: "DanceExample",

        data() {
            return {
                danceRecords: [],
                date_options: {weekday: 'long', year: 'numeric', month: 'short', day: 'numeric'},
                quickSearchQuery: '',
            }
        },

        methods: {
            clearQuickSearch() {
                this.quickSearchQuery = '';
            },
            getDanceRecords() {
                let app = this;
                axios.get('/dances')
                    .then(function (resp) {
                        console.log(resp.data);

                        app.danceRecords = resp.data;
                    })
                    .catch(function (resp) {
                        // console.log(app.danceRecords);
                        alert("Could not load dance records");
                    });

            }
        },

        computed: {
            filteredRecords() {
                let data = this.danceRecords;
                this.quickSearchQuery = this.quickSearchQuery.toLowerCase();
                data = data.filter(dance => dance.name.toLowerCase().indexOf(this.quickSearchQuery) > -1);

                return data;
            },
        },

        mounted() {
            this.$nextTick(function () {
                this.getDanceRecords();
            });
        }
    }
</script>

<style scoped>

</style>
