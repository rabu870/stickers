var vm = new Vue({
    el: "#conflicts-page",
    data: {
        welcome: "Welcome to PSCS Conflicts!"
    },
    methods: {
        verify: function () {
            axios.get('./backend/verify.php?client=true')
            .then(function (response) {
                if (response.data == '0' || response.data == '1') {
                    window.location.href = 'login/';
                }
            });
        },
    },
    beforeMount() {
        this.verify();
    }
});
