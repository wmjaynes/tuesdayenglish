<template>

    <div>
        <div class="alert alert-danger" v-if="reloading">
            The dance database needs to be reloaded. This should take less than a minute...
            <div class="progress">
                <div aria-valuemax="100"
                     aria-valuemin="0"
                     aria-valuenow="100"
                     class="progress-bar progress-bar-striped bg-danger progress-bar-animated"
                     role="progressbar"
                     style="width: 100%"></div>
            </div>
        </div>

        <div class="card" v-for="(date, index) in this.byDate">
            <div class="card-header font-weight-bold">
                {{ (new Date(index)).toLocaleDateString("en-US", date_options_long) }}
            </div>
            <div class="card-body">

                <template class="my-row" v-for="(dance, index) in date">
                    <div class="row border-left">
                        <div class="col col-lg-3 dance-name" v-on:click='toggleHistory(dance.dance_name)'>
                            {{dance.dance_name}}
                        </div>
                        <div class="col col-lg-3">
                            {{dance.caller_name}}
                        </div>
                        <div class="col-1">
                            {{danceCount[dance.dance_name]}}
                        </div>
                    </div>
                    <template v-if="dances[dance.dance_name].displayHistory">
                        <div class="row border-left" v-for="caller in dances[dance.dance_name].callers">
                            <div class="col-6 col-md-3 col-lg-2 pl-4 border-left">
                                {{ (new Date(caller.pivot.date_of)).toLocaleDateString("en-US", date_options_short) }}
                            </div>
                            <div class="col">
                                {{caller.name}}
                            </div>
                        </div>
                    </template>
                </template>
            </div>
        </div>

    </div>

</template>

<script>
    export default {
        // name: "DanceExample",

        data() {
            return {
                danceCount: [],
                byDate: [],
                dances: [],
                date_options_long: {weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'},
                date_options_short: {weekday: 'short', year: 'numeric', month: 'short', day: 'numeric'},
                doReload: false,
                reloading: false,
            }
        },

        methods: {
            toggleHistory(danceName) {
                let dh = this.dances[danceName].displayHistory;
                this.dances[danceName].displayHistory = !dh;
                console.log("displayHistory: " + this.dances[danceName].displayHistory);
            },
            getDanceRecords() {
                let app = this;
                axios.get('/dancesbydate')
                    .then(function (resp) {
                        // console.log(resp.data);

                        app.danceCount = resp.data.danceCount;
                        app.byDate = resp.data.byDate;
                        app.dances = resp.data.dances;
                    })
                    .catch(function (resp) {
                        // console.log(resp);
                        alert("Could not load dance records");
                    });

            },

            async reloadDatabase() {
                if (!this.doReload)
                    return;
                let app = this;
                app.reloading = true;
                await axios.get('/reload')
                    .then(function (resp) {
                        app.reloading = false;
                    })
                    .catch(function (resp) {
                        alert('Error while reloading dance records')
                    })
            },

            async checkOnSource() {
                let app = this;
                await axios.get('/doupdatequery')
                    .then(function (resp) {
                        // console.log('doupdatequery: ' + resp.data);
                        if (resp.data) {
                            // alert("The dances done data needs to be refreshed.");
                            // window.location.replace('/reload');
                            app.doReload = true;
                        }
                    })
                    .catch(function (resp) {
                        // console.log(resp);
                        alert("Could not check on spreadsheet");
                    });
            },

            async doMounted() {
                await this.checkOnSource();
                await this.reloadDatabase();
                this.getDanceRecords();
            }
        },

        mounted() {
            this.doMounted();
        }
    }
</script>

<style scoped>
    .row:nth-child(odd) {
        background-color: rgba(0, 0, 0, 0.03);
    }

    .dance-name {
        cursor: pointer;
    }

</style>
