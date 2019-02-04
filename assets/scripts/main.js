var vm = new Vue({
    el: '#main-page',
    data: {},
    methods: {
        verify: function () {
            axios
                .get('./backend/verify.php?client=true')
                .then(function (response) {
                    if (response.data == '0' || response.data == '1') {
                        window.location.href = 'login/';
                    }
                });
        },
        query: function () {
            var self = this;
            axios.get('./backend/main.php?func=load').then(function (response) {

            });
        }
    },
    beforeMount() {
        this.verify();
        this.query();
    }
});