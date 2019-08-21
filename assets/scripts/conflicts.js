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
        notSelected: [],
        stickers: [],
        students: [],
        slot: [],
        finalConflicts: [],
        results: false
    },
    computed: {
        isDisabled: function () {
            if (this.slot.length == 0) {
                return 'btn btn-primary disabled';
            } else {
                return 'btn btn-primary';
            }
        }
    },
    methods: {
        query: function () {
            var self = this;
            axios.get('./backend/conflicts.php?func=load').then(function (response) {
                if (response.data !== "0") {
                    //compile list of classes
                    var classList = [];
                    response.data[0].forEach(currentClass => {
                        classList.push({
                            id: parseInt(currentClass[0]),
                            className: currentClass[1],
                        })
                    });
                    self.classes = classList;
                    self.notSelected = classList;

                    //compile list of students

                    var studentList = [];
                    response.data[1].forEach(student => {
                        studentList.push({
                            id: parseInt(student[0]),
                            firstName: student[1],
                            lastName: student[2]
                        })
                    });
                    self.students = studentList;


                    $('.main-loader').css('display', 'none');
                    $('.pad').css('display', 'block');
                }
            });
        },
        fetchStickers: function () {
            let self = this;
            axios.get('./backend/conflicts.php?func=stickers&slot=' + JSON.stringify(self.slot.map(x => x.id))).then(function (response) {
                var stickerList = [];
                response.data.forEach(sticker => {
                    stickerList.push({
                        stickerId: parseInt(sticker[0]),
                        studentId: parseInt(sticker[1]),
                        classId: parseInt(sticker[2]),
                        priority: parseInt(sticker[3]),
                        isBlock: parseInt(sticker[4]) == 1 ? true : false
                    });
                });

                self.stickers = stickerList;

                self.genConflicts();
            });
        },
        genConflicts: function () {
            let self = this;

            self.finalConflicts = [];

            let conflicts = [];
            let classes = [];
            classes = _.groupBy(this.stickers, 'classId');

            Object.keys(classes).forEach(cls => {
                classes[cls].forEach(sticker => {
                    if (conflicts[sticker.studentId]) {
                        conflicts[sticker.studentId].push(sticker.stickerId);
                    } else {
                        conflicts[sticker.studentId] = [sticker.stickerId];
                    }
                });
            });
            //console.log(self.classes);

            conflicts.forEach((conflict, i) => {
                if (conflict.length > 1) {
                    let text = "" + self.students.find(x => x.id == i).firstName + " " + self.students.find(x => x.id == i).lastName.substr(1, 1).toUpperCase() + ": ";
                    conflict.forEach((sticker, index) => {
                        self.stickers.find(x => x.stickerId == sticker)
                        let temp = self.stickers.find(x => x.stickerId == sticker);
                        text += self.classes.find(x => x.id === temp.classId).className;
                        text += " (";
                        text += temp.priority == 1 ? "B" : temp.priority == 2 ? "G" : "W";
                        text += ")";
                        text += index != conflict.length - 1 ? " / " : "";
                    });

                    self.finalConflicts.push({
                        stickers: conflict,
                        text: text
                    });
                }
            });

            self.results = true;
        },
        onEnd: function () {
            this.results = false;
        }
    },
    beforeMount() {
        this.query();
    }
});