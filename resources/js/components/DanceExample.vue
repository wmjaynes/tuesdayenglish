<template>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Dance Records</h5>
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th scope="col">Dance Name</th>
                    <th scope="col">First</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="row in this.danceRecords">
                    <td>{{row[0]}}</td>
                    <td>{{row[1]}}</td>
                </tr>

                </tbody>
            </table>


            <ul class="list-group list-group-flush">
                <li class="list-group-item" v-for="row in this.danceRecords">
                    {{row}}
                </li>
            </ul>
        </div>
    </div>

</template>

<script>
    export default {
        // name: "DanceExample",

        data() {
            return {
                danceRecords: [],
            }
        },

        methods: {
            getDanceRecords() {
                let app = this;
                axios.get('https://sheets.googleapis.com/v4/spreadsheets/1ycT5Is3353nnLcShj4owOxzAOq43w3tj9D0xsgcNMVg/values/Leaders?key=AIzaSyDiqFMguLTAdtu6Tmv39pQcZZaAdWAIhqc')
                    .then(function (resp) {
                        console.log(resp.data.values);

                        app.danceRecords = resp.data.values;
                        app.danceRecords.shift();
                    })
                    .catch(function (resp) {
                        console.log(app.danceRecords.values);
                        alert("Could not load dance records");
                    });

            }
        },
        // https://sheets.googleapis.com/v4/spreadsheets/1ycT5Is3353nnLcShj4owOxzAOq43w3tj9D0xsgcNMVg/values/Master?key=AIzaSyDiqFMguLTAdtu6Tmv39pQcZZaAdWAIhqc

        mounted() {
            this.$nextTick(function () {
                this.getDanceRecords();
            });
        }
    }
</script>

<style scoped>

</style>
