function getCookieValue(a) {
    var b = document.cookie.match('(^|;)\\s*' + a + '\\s*=\\s*([^;]+)');
    return b ? b.pop() : '';
}

String.prototype.toTitleCase = function () {
    'use strict'
    var smallWords = /^(a|an|and|as|at|but|by|en|for|if|in|nor|of|on|or|per|the|to|v.?|vs.?|via)$/i
    var alphanumericPattern = /([A-Za-z0-9\u00C0-\u00FF])/
    var wordSeparators = /([ :–—-])/

    return this.split(wordSeparators)
        .map(function (current, index, array) {
            if (
                /* Check for small words */
                current.search(smallWords) > -1 &&
                /* Skip first and last word */
                index !== 0 &&
                index !== array.length - 1 &&
                /* Ignore title end and subtitle start */
                array[index - 3] !== ':' &&
                array[index + 1] !== ':' &&
                /* Ignore small words that start a hyphenated phrase */
                (array[index + 1] !== '-' ||
                    (array[index - 1] === '-' && array[index + 1] === '-'))
            ) {
                return current.toLowerCase()
            }

            /* Ignore intentional capitalization */
            if (current.substr(1).search(/[A-Z]|\../) > -1) {
                return current
            }

            /* Ignore URLs */
            if (array[index + 1] === ':' && array[index + 2] !== '') {
                return current
            }

            /* Capitalize the first letter */
            return current.replace(alphanumericPattern, function (match) {
                return match.toUpperCase()
            })
        })
        .join('')
}

String.prototype.truncate =
    function (n) {
        return this.substr(0, n - 1) + (this.length > n ? '&hellip;' : '');
    };


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
        stickers: [],
        imgurl: "",
        init: false,
        edited: false,
        hs: false,
        flagWarning: ""
    },
    computed: {
        areBlockClasses: function () {
            return this.notStickeredBlock ? this.notStickeredBlock.length > 0 : false || this.stickers[3].length > 0 || this.stickers[4].length > 0 || this.stickers[5].length > 0;
        }
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
                            facilitator: currentClass[3].toTitleCase(),
                            isMega: parseInt(currentClass[4]) == 1 ? true : false,
                            isBlock: parseInt(currentClass[5]) == 1 ? true : false,
                            tags: currentClass[6],
                            mature: parseInt(currentClass[7]) == 1 ? true : false
                        })
                    });
                    // response.data[1].forEach(currentClass => {
                    //     classList.push({
                    //         id: parseInt(currentClass[0]),
                    //         className: currentClass[1],
                    //         link: currentClass[2],
                    //         facilitator: currentClass[3].toTitleCase(),
                    //         isMega: parseInt(currentClass[4]) == 1 ? true : false,
                    //         isBlock: parseInt(currentClass[5]) == 1 ? true : false,
                    //         tags: currentClass[6],
                    //         mature: parseInt(currentClass[7]) == 1 ? true : false
                    //     })
                    // });
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

                    //figure out which classes aren't stickered
                    self.notStickered = self.classes.filter(tempClass => tempClass.isBlock == false && !self.stickers[0].find(x => x.id ===
                        tempClass.id) && !self.stickers[1].find(x => x.id ===
                        tempClass.id) && !self.stickers[2].find(x => x.id ===
                        tempClass.id));
                    self.notStickeredBlock = self.classes.filter(tempClass => tempClass.isBlock == true && !self.stickers[3].find(x => x.id ===
                        tempClass.id) && !self.stickers[4].find(x => x.id ===
                        tempClass.id) && !self.stickers[5].find(x => x.id ===
                        tempClass.id));

                    self.hs = parseInt(response.data[3]) == 1 ? true : false;
                    self.flagWarning = self.hs ? "MS only class." : "HS only class.";

                    self.imgurl = decodeURIComponent(getCookieValue('pic'));
                    if (self.init == false) {
                        $('.nav-tabs').append('<section class="navbar-center"><img src="./assets/media/logo.svg" style="width: 40px; height: 40px;"></section><section style="height: 42px" class="navbar-section">' + (getCookieValue('staff_pwd') !== '' ? '<span style="margin-right: 5px">Stickering as ' + response.data[4] + '</span>' : '') + '<a href="./login" class="no-outline tooltip tooltip-left" data-tooltip="Log out"><figure class="avatar" style="height: 33px; width: 33px; margin-right: 10px; margin-bottom: 4px;"><img src="' + self.imgurl + '"></figure></a></section>');
                        $('.nav-tabs-navigation').fadeIn();
                    }

                    if (self.areBlockClasses) {
                        $('.tab-item').each(function () {
                            if ($(this).attr('id') == 't-Block') {
                                $(this).attr('style', 'display: block;');

                                $($($(this).children()[0]).children()[0]).html('Mega'); //REMOVE WHEN MEGAS ARE NOT STICKERED SEPERATELY
                            }
                        });
                    } else {
                        $('.tab-item').each(function () {
                            if ($(this).attr('id') == 't-Block') {
                                $(this).attr('style', 'display: none;');
                            }
                        });
                    }
                    // $('.main-loader').css('display', 'none');
                    // $('.pad').css('display', 'block');
                    $('.main-loader').fadeOut();
                    $('.pad').fadeIn();
                    self.init = true;
                } else {
                    alert("Stickering isn't currently available.");
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
                self.edited = false;
                $('.update-button').removeClass('loading');
                $('.update-button').html('Changes saved!');
                setTimeout(
                    function () {
                        $('.update-button').html('Save changes');
                    }, 3000);
            }).catch(function () {
                alert('Error! Your changes were not saved. Login again.');
                window.location.href = './login';
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
                if (item.mature) {
                    res += 'tag-4 ';
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
                if (item.mature) {
                    res += 'tag-7 ';
                }
                if (res == "") {
                    res = 'tag-10';
                }
            }
            return res;
        },
        genTags: function (item) {
            var res = [];
            if (item) {

                // if (item.isMega) {
                //     res.push('Mega');
                // }
                if (item.tags.indexOf('hs-only') != -1) {
                    res.push('HS only');
                }
                if (item.tags.indexOf('ms-only') != -1) {
                    res.push('MS only');
                }
                if (item.mature) {
                    res.push('Mature themes');
                }
                return res;
            }
        },
        onEnd: function () {
            this.edited = true;
            $('.search-reg').val('');
            $('.search-block').val('');
            this.chipFilterReg();
            this.chipFilterBlock();
            this.filterReg();
            this.filterBlock();
        },
        chipFilterReg: function () {
            $(".reg-ns").each(function () {
                if ($(this).attr('data-tag').includes($('input[name=filter-radio-rg]:checked').attr('id')) || $('input[name=filter-radio-rg]:checked').attr('id').includes('tag-0')) {
                    $(this).show().removeClass('hidden-tags');
                } else {
                    $(this).hide().addClass('hidden-tags');
                }
            });
            this.filterReg();
        },
        filterReg: function () {
            var val = $('.search-reg').val();
            $('.none-found-r').remove();
            if (val != '') {
                var temp = this.notStickered;
                new Fuse(this.notStickered, {
                    threshold: 0.3,
                    location: 0,
                    distance: 100,
                    maxPatternLength: 32,
                    minMatchCharLength: 1,
                    keys: [
                        "className",
                        "facilitator"
                    ]
                }).search(val).forEach(item => {
                    temp = temp.filter(cls => item.id !== cls.id);
                    id = '#block-ns-' + item.id;
                    if (!$(id).hasClass('hidden-tags')) {
                        $(id).show();
                    }
                });

                temp.forEach(cls => {
                    id = '#block-ns-' + cls.id;
                    if (!$(id).hasClass('hidden-tags')) {
                        $(id).hide();
                    }
                });

                if (temp.length == this.notStickered.length) {
                    $('.reg-ns-container').append('<div class="menu-item none-found-r">No results found.</div>');
                }
            } else {
                $(".reg-ns").each(function () {
                    if (!$(this).hasClass('hidden-tags')) {
                        $(this).show();
                    }
                });
            }
        },
        chipFilterBlock: function () {
            $(".block-ns").each(function () {
                if ($(this).attr('data-tag').includes($('input[name=filter-radio-b]:checked').attr('id')) || $('input[name=filter-radio-b]:checked').attr('id').includes('tag-10')) {
                    $(this).show().removeClass('hidden-tags-b');
                } else {
                    $(this).hide().addClass('hidden-tags-b');
                }
            });
            this.filterBlock();
        },
        filterBlock: function () {
            var val = $('.search-block').val();
            $('.none-found-b').remove();
            if (val != '') {
                var temp = this.notStickeredBlock;
                new Fuse(this.notStickeredBlock, {
                    threshold: 0.3,
                    location: 0,
                    distance: 100,
                    maxPatternLength: 32,
                    minMatchCharLength: 1,
                    keys: [
                        "className",
                        "facilitator"
                    ]
                }).search(val).forEach(item => {
                    temp = temp.filter(cls => item.id !== cls.id);
                    id = '#block-ns-' + item.id;
                    if (!$(id).hasClass('hidden-tags-b')) {
                        $(id).show();
                    }
                });

                temp.forEach(cls => {
                    id = '#block-ns-' + cls.id;
                    if (!$(id).hasClass('hidden-tags-b')) {
                        $(id).hide();
                    }
                })
                if (temp.length == this.notStickeredBlock.length) {
                    $('.block-ns-container').append('<div class="menu-item none-found-b">No results found.</div>');
                }
            } else {
                $(".block-ns").each(function () {
                    if (!$(this).hasClass('hidden-tags-b')) {
                        $(this).show();
                    }
                });
            }
        },
        genID: function (item, type) {
            return type + "-ns-" + item;
        },
        warnings: function (item) {
            if (this.hs) {
                if (item.tags.includes('ms-only')) {
                    return true;
                } else {
                    return false;
                }
            } else {
                if (item.tags.includes('hs-only')) {
                    return true;
                } else {
                    return false;
                }
            }
        }

    },
    beforeMount() {
        this.verify();
        this.query();
    },
    mounted() {
        for (var i = 0; i < 10; i++) {
            console.log(i + " SECRET ADMIN PAGE AT https://stickers.pscs.org/admin");
        }

        var self = this;
        $('.nav-tabs').arrive("#t-Block", function () {
            $('.tab-section').append('<li class="no-margin-button"><button class="update-button btn btn-primary btn-sm">Save changes</button></li>');
            $('.update-button').click(function () {
                if (!self.stickers[3].length && !self.stickers[4].length && !self.stickers[5].length && this.areBlockClasses) {
                    $('.confirmation-modal').addClass('active');
                } else {
                    self.update();
                }
            });
        });
        // var once = true;
        // $(document).arrive('.reg-ns', function () {
        //     if (once) {
        //         hopscotch.startTour({
        //             id: "main-page",
        //             steps: [{
        //                 title: "Drag and drop",
        //                 content: "Drag classes to the appropriate section on the right. Try it!",
        //                 target: document.querySelectorAll('.reg-ns')[0],
        //                 placement: "bottom"
        //             }]
        //         });
        //         once = false;
        //     }
        // });


        $('.cancel-save').click(function () {
            $('.confirmation-modal').removeClass('active');
        });

        $('.save-anyway').click(function () {
            $('.confirmation-modal').removeClass('active');
            self.update();
        })

        $(window).bind('beforeunload', function () {
            if (self.edited == true) {
                return "You have unsaved changes, are you sure you want to leave?";
            }
        });
    }
});