<template>

    <div>
        <div class="pl-0">
            Callers who called in the last
            <input @blur="getDanceRecords" size="2" type="text" v-model="danceRange">
            month(s); <br> Dance history for the last
            <input @blur="getDanceRecords" size="2" type="text" v-model="historyRange">
            month(s);
            <form class="form-inline">
                <input class="form-control"
                       placeholder="Quick search on caller name"
                       size="40"
                       type="text"
                       v-model="quickSearchQuery">
                <button @click.prevent="clearQuickSearch();" class="btn btn-secondary">Clear</button>
            </form>
        </div>

        <div v-for="caller in filteredRecords">
            <div class="row border-top dance-name">
                <div class="col-12 col-md-5 font-weight-bold">{{caller.name}} -- Total: {{caller.dances.length}}</div>
            </div>
            <div class="row" v-for="dance in caller.dances">
                <div class="col-1"></div>
                <div class="col-6 col-md-3 ">
                    {{ (new Date(dance.pivot.date_of)).toLocaleDateString("en-US", date_options) }}
                </div>
                <div class="col">
                    {{dance.name}}
                </div>
            </div>
        </div>

    </div>

</template>

<script>
    export default {

        data() {
            return {
                danceRecords: [],
                date_options: {weekday: 'short', year: 'numeric', month: 'short', day: 'numeric'},
                quickSearchQuery: '',
                danceRange: 3,
                historyRange: 12,
            }
        },

        methods: {
            clearQuickSearch() {
                this.quickSearchQuery = '';
            },
            getDanceRecords() {
                let app = this;
                axios.get('/callers' + '?danceRange=' + this.danceRange + '&historyRange=' + this.historyRange)
                    .then(function (resp) {
                        // console.log(resp.data);

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
                data = data.filter(caller => caller.name.toLowerCase().indexOf(this.quickSearchQuery) > -1);

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

    .dance-name {
        background-color: #e9ecef;
    }

</style>
