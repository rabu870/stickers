function getCookieValue(a) {
    var b = document.cookie.match('(^|;)\\s*' + a + '\\s*=\\s*([^;]+)');
    return b ? b.pop() : '';
}

String.prototype.truncate =
    function (n) {
        return this.substr(0, n - 1) + (this.length > n ? '&hellip;' : '');
    };

var vm = new Vue({
    el: '#main-page',
    data: {
        classes: [],
        stickers: [],
        students: [],
        slot: [2301, 2302, 2303]
    },
    methods: {
        query: function () {
            var self = this;
            axios.get('./backend/conflicts.php?func=load').then(function (response) {
                if (response.data !== "0") {
                    //compile list of classes
                    var classList = [];
                    response.data[1].forEach(currentClass => {
                        classList.push({
                            id: parseInt(currentClass[0]),
                            className: currentClass[1],
                        })
                    });
                    self.classes = classList;

                    //compile list of students

                    var studentList = [];
                    response.data[2].forEach(student => {
                        studentList.push({
                            id: parseInt(student[0]),
                            firstName: student[1],
                            lastName: student[2]
                        })
                    });
                    self.students = studentList;

                    //put the stickers in an array for each class
                    var stickerList = [];
                    response.data[0].forEach((category, index) => {
                        stickerList[index] = [parseInt(category[0]), []];
                        category[1].forEach(sticker => {
                            stickerList[index][1] = sticker;
                        })
                    });

                    self.stickers = stickerList;

                    $('.main-loader').css('display', 'none');
                    $('.pad').css('display', 'block');
                }
            });
        },
        genConflicts: function () {
            let self = this;
            let conflicts = []
            this.slot.forEach(item => {
                conflicts = conflicts.concat(self.classes.find(obj => obj.id == item));
            });
            console.log(conflicts);
        }
    },
    beforeMount() {
        this.query();
    }
});