var vm = new Vue({
    el: '#main-page',
    data: {
        classes: Array,
        blacksAllotted: Number,
        greysAllotted: Number,
        blacksAllottedBlock: Number,
        greysAllottedBlock: Number,
        stickers: Array
    },
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
                if (response.data !== "0") {
                    //compile list of classes
                    var classList = [];
                    response.data[1].forEach(currentClass => {
                        classList.push({
                            id: currentClass.id,
                            className: currentClass.class_name,
                            link: currentClass.link,
                            facilitator: currentClass.facilitator,
                            isMega: currentClass.is_mega,
                            isBlock: currentClass.is_block
                        })
                    });
                    self.classes = classList;

                    //parse the allotted stickers

                    self.blacksAllotted = parseInt(response.data[2][0]);
                    self.greysAllotted = parseInt(response.data[2][1]);
                    self.blacksAllottedBlock = parseInt(response.data[2][3]);
                    self.greysAllottedBlock = parseInt(response.data[2][4]);

                    //put the stickers for this student in their respective arrays
                    var stickerList = [];
                    response.data[0].forEach((category, index) => {
                        stickerList[index] = [];
                        category.forEach(sticker => {
                            stickerList[index].push(parseInt(sticker));
                        })
                    });

                    self.stickers = stickerList;

                    //axios.get('./backend/main.php?func=update&stickers=' + JSON.stringify(self.stickers));
                }
            });
        }
    },
    beforeMount() {
        this.verify();
        this.query();
    }
});