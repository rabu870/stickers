var vm = new Vue({
    el: '#main-page',
    data: {
        classes: [],
        blacksAllotted: Number,
        greysAllotted: Number,
        blacksAllottedBlock: Number,
        greysAllottedBlock: Number,
        notStickered: [],
        notStickeredBlock: [],
        stickers: []
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
                            id: parseInt(currentClass[0]),
                            className: currentClass[1],
                            link: currentClass[2],
                            facilitator: currentClass[3],
                            isMega: parseInt(currentClass[4]) == 1 ? true : false,
                            isBlock: parseInt(currentClass[5]) == 1 ? true : false,
                            tags: currentClass[6]
                        })
                    });
                    self.classes = classList;

                    //parse the allotted stickers

                    self.blacksAllotted = parseInt(response.data[2][0]);
                    self.greysAllotted = parseInt(response.data[2][1]);
                    self.blacksAllottedBlock = parseInt(response.data[2][2]);
                    self.greysAllottedBlock = parseInt(response.data[2][3]);

                    //put the stickers for this student in their respective arrays
                    var stickerList = [];
                    response.data[0].forEach((category, index) => {
                        stickerList[index] = [];
                        category.forEach(sticker => {
                            stickerList[index].push(self.classes.find(x => x.id ===
                                parseInt(sticker)));
                        })
                    });

                    self.stickers = stickerList;

                    self.notStickered = self.classes.filter(tempClass => tempClass.isBlock == false && !self.stickers[0].find(x => x.id ===
                        tempClass.id) && !self.stickers[1].find(x => x.id ===
                        tempClass.id) && !self.stickers[2].find(x => x.id ===
                        tempClass.id));
                    self.notStickeredBlock = self.classes.filter(tempClass => tempClass.isBlock == true && !self.stickers[3].find(x => x.id ===
                        tempClass.id) && !self.stickers[4].find(x => x.id ===
                        tempClass.id) && !self.stickers[5].find(x => x.id ===
                        tempClass.id));
                }
            });
        },
        update: function () {
            self = this;
            $('.update-button').addClass('loading');
            var stickerList = [];
            self.stickers.forEach((category, index) => {
                stickerList[index] = category.map(x => x.id);
            });
            axios.get('./backend/main.php?func=update&stickers=' + JSON.stringify(stickerList)).then(function () {
                self.query();
                $('.update-button').removeClass('loading');
                $('.update-button').html('Changes saved!');
                setTimeout(
                    function () {
                        $('.update-button').html('Save changes');
                    }, 3000);
            });
        },
        checkTags: function (item) {
            var res = "";
            if (item) {
                if (item.isMega) {
                    res += 'tag-3 ';
                }
                if (item.tags.indexOf('hs-only') != -1) {
                    res += 'tag-1 ';
                }
                if (item.tags.indexOf('ms-only') != -1) {
                    res += 'tag-2 ';
                }
                if (res == "") {
                    res = 'tag-0';
                }
            }
            return res;
        },
        checkTagsBlock: function (item) {
            var res = "";
            if (item) {
                if (item.tags.indexOf('hs-only') != -1) {
                    res += 'tag-5 ';
                }
                if (item.tags.indexOf('ms-only') != -1) {
                    res += 'tag-6 ';
                }
                if (res == "") {
                    res = 'tag-10';
                }
            }
            return res;
        }
    },
    beforeMount() {
        this.verify();
        this.query();
    },
    mounted() {
        var self = this;
        $('.nav-tabs').arrive("#t-Block", function () {
            $('.nav-tabs').append('<li class="no-margin-button"><button class="update-button btn btn-primary btn-sm">Save changes</button></li>');
            $('.update-button').click(function () {
                self.update();
            });
        });
    }
});