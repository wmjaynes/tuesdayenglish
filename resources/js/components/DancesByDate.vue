<template>

    <div>

        <div class="card" v-for="(date, index) in this.byDate">
            <div class="card-header">
                {{ (new Date(index)).toLocaleDateString("en-US", date_options) }}
            </div>
            <div class="card-body">
                <table class="table table-sm  table-bordered">
                    <tbody>
                    <tr v-for="dance in date">
                        <td class="w-50">{{dance.dance_name}}</td>
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
                date_options: {weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'},
            }
        },

        methods: {
            getDanceRecords() {
                let app = this;
                axios.get('/dancesbydate')
                    .then(function (resp) {
                        console.log(resp.data);

                        app.danceCount = resp.data.danceCount;
                        app.byDate = resp.data.byDate;
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

</style>
