<template>

    <div>
        <div class="pl-0">
            Dances done in the last
            <input @blur="getDanceRecords" size="2" type="text" v-model="danceRange">
            month(s); <br> Dance history for the last
            <input @blur="getDanceRecords" size="2" type="text" v-model="historyRange">
            month(s);
            <form class="form-inline">
                <input class="form-control"
                       placeholder="Quick search on dance name"
                       size="40"
                       type="text"
                       v-model="quickSearchQuery">
                <button @click.prevent="clearQuickSearch();" class="btn btn-secondary">Clear</button>
            </form>
        </div>

        <div v-for="dance in filteredRecords">
            <div class="row border-top dance-name">
                <div class="col-12 col-md-5 font-weight-bold">{{dance.name}}</div>
                <div class="col-3 col-md-2">{{ dance.meter }}</div>
                <div class="col-3 col-md-2">{{ dance.key }}</div>
                <div class="col-3 col-md-1">{{ dance.formation }}</div>
                <div class="col-3 col-md-2">{{ dance.barnes }}</div>
            </div>
            <div class="row" v-for="caller in dance.callers">
                <div class="col-1"></div>
                <div class="col-6 col-md-3 ">
                    {{ (new Date(caller.pivot.date_of)).toLocaleDateString("en-US", date_options) }}
                </div>
                <div class="col">
                    {{caller.name}}
                </div>
            </div>
        </div>

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
                axios.get('/dances' + '?danceRange=' + this.danceRange + '&historyRange=' + this.historyRange)
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

    .dance-name {
        background-color: #e9ecef;
    }

</style>
