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
        results: false,
        isFullscreen: false
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

                    classList.sort((a, b) => (a.className > b.className) ? 1 : -1);
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
                            text += "<span class='right-divider'>";
                            text += self.classes.find(x => x.id === temp.classId).className;
                            text += " (";
                            text += temp.priority == 1 ? "B" : temp.priority == 2 ? "G" : "W";
                            text += ")";
                            text += "</span>";
                            //text += self.includeWhiteStickers ? index != conflict.length - 1 ? " / " : "" : index != conflict.filter(x => self.stickers.find(p => p.stickerId == x).priority != 3).length - 1 ? " / " : "";
                        }
                        console.log(conflict.filter(x => self.stickers.find(p => p.stickerId == x).priority != 3));
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
            // if ($('.is-block-checkbox').is(':checked')) {
            //     $('.unselected-classes').each(function () {
            //         if ($(this).attr('data-block') == 'true') {
            //             $(this).show();
            //         } else {
            //             $(this).hide();
            //         }
            //     });
            // } else {
            //     $('.unselected-classes').each(function () {
            //         if ($(this).attr('data-block') == 'true') {
            //             $(this).hide();
            //         } else {
            //             $(this).show();
            //         }

            //     });
            // }
        },
        resize: function () {
            if (this.isFullscreen) {
                $('.resize-icon').addClass('icon-plus').removeClass('icon-minus');
                $('.menu-results').attr('style', 'position: fixed; top: 52%; right: 20px; width: 47%; bottom: 10px; overflow-y: scroll; overflow-x:hidden;');
                this.isFullscreen = false;
            } else {
                console.log($('.resize-icon'));
                $('.resize-icon').addClass('icon-minus').removeClass('icon-plus');
                $('.menu-results').attr('style', 'position: fixed; top: 10px; right: 20px; left: 15px; bottom: 10px; overflow-y: scroll; overflow-x:hidden;');
                this.isFullscreen = true;
            }

        },
        customClass: function () {
            var self = this;

            (async function getFormValues() {
                var availableTags = [
                    "ActionScript",
                    "AppleScript",
                    "Asp",
                    "BASIC",
                    "C",
                    "C++",
                    "Clojure",
                    "COBOL",
                    "ColdFusion",
                    "Erlang",
                    "Fortran",
                    "Groovy",
                    "Haskell",
                    "Java",
                    "JavaScript",
                    "Lisp",
                    "Perl",
                    "PHP",
                    "Python",
                    "Ruby",
                    "Scala",
                    "Scheme"
                ];

                function split(val) {
                    return val.split(/,\s*/);
                }

                function extractLast(term) {
                    return split(term).pop();
                }
                $(document).arrive('#tags', function () {
                    alert('test');
                    $("#tags")
                        // don't navigate away from the field on tab when selecting an item
                        .on("keydown", function (event) {
                            if (event.keyCode === $.ui.keyCode.TAB &&
                                $(this).autocomplete("instance").menu.active) {
                                event.preventDefault();
                            }
                        })
                        .autocomplete({
                            minLength: 0,
                            source: function (request, response) {
                                // delegate back to autocomplete, but extract the last term
                                response($.ui.autocomplete.filter(
                                    availableTags, extractLast(request.term)));
                            },
                            focus: function () {
                                // prevent value inserted on focus
                                return false;
                            },
                            select: function (event, ui) {
                                var terms = split(this.value);
                                // remove the current input
                                terms.pop();
                                // add the selected item
                                terms.push(ui.item.value);
                                // add placeholder to get the comma-and-space at the end
                                terms.push("");
                                this.value = terms.join(", ");
                                return false;
                            }
                        });
                });
                const {
                    value: formValues
                } = await Swal.fire({
                    title: 'Add a custom class or advising',
                    html: '<div class="form-group">' +
                        '<input class="form-input" type="text" id="add-name" placeholder="Name"><br>' +
                        '<input class="form-input" type="text" id="add-black-stickers" placeholder="Black stickers"><br>' +
                        '<input class="form-input" type="text" id="add-grey-stickers" placeholder="Grey stickers"><br>' +
                        '<input class="form-input" type="text" id="add-white-stickers" placeholder="White stickers"><br>' +
                        '<input class="form-input" type="text" id="tags" placeholder="White stickers"><br>' +
                        `<label class="form-checkbox">
                        Save class locally so you can use it again after you close this page
                        <input class="form-input" id="add-save-local" type="checkbox"><i class="form-icon"></i>
                    </label>`,
                    focusConfirm: false,
                    showCloseButton: false,
                    showCancelButton: true,
                    focusConfirm: false,
                    confirmButtonText: '<i class="icon icon-check"></i>',
                    confirmButtonAriaLabel: 'Add class',
                    confirmButtonColor: '#4b48d6',
                    cancelButtonText: '<i class="icon icon-cross"></i>',
                    cancelButtonAriaLabel: 'Cancel',
                    cancelButtonColor: '#aaa',
                    preConfirm: () => {
                        return [
                            $('#add-name').val(),
                            $('#add-black-stickers').val(),
                            $('#add-grey-stickers').val(),
                            $('#add-white-stickers').val(),
                            $('#add-save-local').prop("checked") == true,
                            $('#tags')
                        ];
                    }
                });

                if (formValues) {
                    self.$root.students.push({
                        id: '',
                        firstName: formValues[0],
                        lastName: formValues[1],
                        email: formValues[2],
                        hs: formValues[3],
                        loginKey: ''
                    });
                }
            })();
        }
    },
    beforeMount() {
        this.query();
    }
});