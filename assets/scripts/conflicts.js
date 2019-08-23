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
        includeWhiteStickers: false,
        finalConflicts: [],
        results: false
    },
    computed: {
        isDisabled: function () {
            if (this.slot.length == 0) {
                return 'conflict-button btn btn-primary disabled';
            } else {
                return 'conflict-button btn btn-primary';
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
                            isBlock: parseInt(currentClass[5]) == 1 ? true : false
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

                    self.filterClasses();

                    $('.main-loader').css('display', 'none');
                    $('.pad').css('display', 'block');
                }
            });
        },
        fetchStickers: function () {
            $('.conflict-button').addClass('loading');
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

            //group the stickers by class
            classes = _.groupBy(this.stickers, 'classId');

            //sort the stickers into an array for each student
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
                //if there is a conflict, and if include white stickers is off, check if there is a conflict without including white stickers
                if (conflict.length > 1 && self.includeWhiteStickers ? true : conflict.filter(x => self.stickers.find(p => p.stickerId == x).priority != 3).length > 1) {
                    let text = "" + self.students.find(x => x.id == i).firstName + " " + self.students.find(x => x.id == i).lastName.charAt(0).toUpperCase() + ": ";
                    conflict.forEach((sticker, index) => {
                        //fetch the sticker based on the stored stickerId
                        let temp = self.stickers.find(x => x.stickerId == sticker);
                        if (self.includeWhiteStickers ? true : temp.priority != 3) {
                            text += self.classes.find(x => x.id === temp.classId).className;
                            text += " (";
                            text += temp.priority == 1 ? "B" : temp.priority == 2 ? "G" : "W";
                            text += ")";
                            text += self.includeWhiteStickers ? index != conflict.length - 1 ? " / " : "" : index != conflict.filter(x => self.stickers.find(p => p.stickerId == x).priority != 3).length - 1 ? " / " : "";
                        }
                    });

                    self.finalConflicts.push({
                        stickers: conflict,
                        text: text
                    });
                }
            });
            $('.conflict-button').removeClass('loading');
            self.results = true;
        },
        onEnd: function () {
            this.filterClasses();
            this.results = false;
        },
        filterClasses: function () {
            //show and hide block/reg classes based on what the user has selected
            if ($('.is-block-checkbox').is(':checked')) {
                $('.unselected-classes').each(function () {
                    if ($(this).attr('data-block') == 'true') {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            } else {
                $('.unselected-classes').each(function () {
                    if ($(this).attr('data-block') == 'true') {
                        $(this).hide();
                    } else {
                        $(this).show();
                    }

                });
            }
        }
    },
    beforeMount() {
        this.query();
    }
});