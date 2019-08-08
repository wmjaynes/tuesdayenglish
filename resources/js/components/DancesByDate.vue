<template>

    <div>

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
                        // console.log(app.danceRecords);
                        alert("Could not load dance records");
                    });

            }
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
        cursor: pointer;
    }

</style>
