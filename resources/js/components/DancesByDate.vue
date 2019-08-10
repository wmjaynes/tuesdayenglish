<template>

    <div>
        <div class="alert alert-danger" v-if="reloading">
            The dance database needs to be reloaded. This should take less than a minute...
            <div class="progress">
                <div class="progress-bar progress-bar-striped bg-danger progress-bar-animated"
                     role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"
                     style="width: 100%"></div>
            </div>
        </div>

        <div class="card" v-for="(date, index) in this.byDate">
            <div class="card-header">
                {{ (new Date(index)).toLocaleDateString("en-US", date_options) }}
            </div>
            <div class="card-body">
                <table class="table table-sm  table-bordered table-striped">
                    <tbody>
                    <tr v-for="(dance, index) in date">
                        <td class="w-50 dance-name" v-on:click='toggleHistory(dance.dance_name)'>
                            {{dance.dance_name}}
                            <dance-history :dance="dances[dance.dance_name]"
                                           v-if="dances[dance.dance_name].displayHistory"></dance-history>
                        </td>
                        <td class="w-50">{{dance.caller_name}}</td>
                        <td>{{danceCount[dance.dance_name]}}</td>
                    </tr>
                    </tbody>
                </table>
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
                date_options: {weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'},
                doReload: false,
                reloading: false,
            }
        },

        methods: {
            toggleHistory(danceName) {
                let dh = this.dances[danceName].displayHistory;
                this.dances[danceName].displayHistory = !dh;
                // console.log("displayHistory: " + this.dances[danceName].displayHistory);
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
    .dance-name {
        cursor: pointer;
    }

</style>
