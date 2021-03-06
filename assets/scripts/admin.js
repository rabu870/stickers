Vue.component('meta-edit', {
	template: `
    <div>
        <table class='student-table table'>
            <tr>
                <th>Stickering active</th>
                <th>Black stickers allotted</th>
                <th>Grey stickers allotted</th>
                <th>Block blacks allotted (or secondary category if applicable)</th>
                <th>Block greys allotted (or secondary category if applicable)</th>
            </tr>
            <tr>
				<td>
					<div class="form-group">
						<label class="form-switch">
						<input type="checkbox" class='form-input' v-model="$root.meta.stickeringActive" @focus='check'>
							<i class="form-icon"></i>
						</label>
					</div>
				</td>
				<td><input type="number" class='form-input' :disabled='$root.stickeringActiveInit' v-model="$root.meta.blacksAllotted" @focus='check'></td>
				<td><input type="number" class='form-input' :disabled='$root.stickeringActiveInit' v-model="$root.meta.greysAllotted" @focus='check'></td>
				<td><input type="number" class='form-input' :disabled='$root.stickeringActiveInit' v-model="$root.meta.blacksAllottedBlock" @focus='check'></td>
				<td><input type="number" class='form-input' :disabled='$root.stickeringActiveInit' v-model="$root.meta.greysAllottedBlock" @focus='check'></td>
            </tr>
        </table>
		<div class='buttons'>
            <button class='btn btn-primary save-changes-button' @click='save'>Save changes</button>
        </div>
    </div>`,
	methods: {
		save: function () {
			var self = this;
			var r = true;
			console.log(self.$root.meta.stickeringActive);
			if (self.$root.meta.stickeringActive && !self.$root.stickeringActiveInit) {
				r = confirm("Are you sure? This will delete all sticker data and reset classes!");
			}
			if (r) {
				$('.save-changes-button').addClass('loading');
				self.$root.meta.stickeringActive =
					self.$root.meta.stickeringActive == true ? '1' : '0';
				axios
					.get(
						'./backend/admin.php?func=meta&meta=' +
						JSON.stringify(self.$root.meta)
					)
					.then(function (response) {
						self.$root.query();
						$('.save-changes-button').removeClass('loading');
						$('.save-changes-button').html('Changes saved!');
					});
			}
		},
		check: function () {
			$('.save-changes-button').html('Save changes');
		}
	}
});

Vue.component('student-table', {
	template: `
    <div>
        <table class='student-table table'>
            <tr>
                <th>First name</th>
                <th>Last name</th>
                <th>Email</th>
                <th>High schooler?</th>
                <th></th>
            </tr>
            <tr v-for="(student,index) in $root.students">
                <td><input type="text" class='form-input' v-model="student.firstName" @focus='check' /></td>
                <td><input type="text" class='form-input' v-model="student.lastName" @focus='check' /></td>
                <td><input type="email" class='form-input' v-model="student.email" @focus='check' /></td>
				<td>
					<div class="form-group">
						<label class="form-switch">
						<input type="checkbox" class='form-input' v-model="student.hs">
							<i class="form-icon"></i>
						</label>
					</div>
				</td>
                <td><button class='btn btn-error btn-action' @click='deletestudent(index)' @focus='check'><i class='icon icon-delete'></i></button></td>
            </tr>
        </table>
        <div class='buttons'>
            <button class='btn btn-primary btn-action' @click='addStudent'><i class='icon icon-plus'></i></button>
            <button class='btn btn-primary save-changes-button' @click='save'>Save changes</button>
        </div>
    </div>`,
	methods: {
		addStudent: function () {
			$('.save-changes-button').html('Save changes');
			var self = this;

			(async function getFormValues() {
				const {
					value: formValues
				} = await Swal.fire({
					title: 'Add a student',
					html: '<div class="form-group">' +
						'<input class="form-input" type="text" id="add-fname" placeholder="First name"><br>' +
						'<input class="form-input" type="text" id="add-lname" placeholder="Last name"><br>' +
						'<input class="form-input" type="email" id="add-email" placeholder="Email"><br>' +
						`<td>
						<div class="form-group">High schooler?
							<label class="form-switch">
								<input type="checkbox" class="form-input" id="add-hs">
								<i class="form-icon"></i>
							</label>
						</div>
					</td>`,
					focusConfirm: false,
					showCloseButton: false,
					showCancelButton: true,
					focusConfirm: false,
					confirmButtonText: '<i class="icon icon-check"></i>',
					confirmButtonAriaLabel: 'Add student',
					confirmButtonColor: '#4b48d6',
					cancelButtonText: '<i class="icon icon-cross"></i>',
					cancelButtonAriaLabel: 'Cancel',
					cancelButtonColor: '#aaa',
					preConfirm: () => {
						return [
							$('#add-fname').val(),
							$('#add-lname').val(),
							$('#add-email').val(),
							$('#add-hs').val() == "on" ? true : false
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
		},
		save: function () {
			$('.save-changes-button').addClass('loading');
			var self = this;
			$.post("./backend/admin.php", {
				func: "students",
				students: JSON.stringify(self.$root.students)
			}).done(function (data) {
				self.$root.query();
				$('.save-changes-button').removeClass('loading');
				$('.save-changes-button').html('Changes saved!');
			});
		},
		deletestudent: function (student) {
			this.$root.students.splice(student, 1);
		},
		check: function () {
			$('.save-changes-button').html('Save changes');
		}
	}
});

Vue.component('admin-table', {
	template: `
    <div>
        <table class='admin-table table'>
            <tr>
                <th>First name</th>
                <th>Last name</th>
                <th>Email</th>
                <th></th>
            </tr>
            <tr v-for="(admin,index) in $root.admin">
                <td><input type="text" class='form-input' v-model="admin.firstName" @focus='check' /></td>
                <td><input type="text" class='form-input' v-model="admin.lastName" @focus='check' /></td>
                <td><input type="email" class='form-input' v-model="admin.email" @focus='check' /></td>
                <td><button class='btn btn-error btn-action' @click='deleteadmin(index)' @focus='check'><i class='icon icon-delete'></i></button></td>
            </tr>
        </table>
        <div class='buttons'>
            <button class='btn btn-primary btn-action' @click='addAdmin'><i class='icon icon-plus'></i></button>
            <button class='btn btn-primary a-save-changes-button' @click='save'>Save changes</button>
        </div>
    </div>`,
	methods: {
		addAdmin: function () {
			$('.a-save-changes-button').html('Save changes');
			var self = this;

			(async function getFormValues() {
				const {
					value: formValues
				} = await Swal.fire({
					title: 'Add an admin',
					html: '<div class="form-group">' +
						'<input class="form-input" type="text" id="a-add-fname" placeholder="First name"><br>' +
						'<input class="form-input" type="text" id="a-add-lname" placeholder="Last name"><br>' +
						'<input class="form-input" type="email" id="a-add-email" placeholder="Email"><br>' +
						'</div>',
					focusConfirm: false,
					showCloseButton: false,
					showCancelButton: true,
					focusConfirm: false,
					confirmButtonText: '<i class="icon icon-check"></i>',
					confirmButtonAriaLabel: 'Add an admin',
					confirmButtonColor: '#4b48d6',
					cancelButtonText: '<i class="icon icon-cross"></i>',
					cancelButtonAriaLabel: 'Cancel',
					cancelButtonColor: '#aaa',
					preConfirm: () => {
						return [
							$('#a-add-fname').val(),
							$('#a-add-lname').val(),
							$('#a-add-email').val()
						];
					}
				});

				if (formValues) {
					self.$root.admin.push({
						id: '',
						firstName: formValues[0],
						lastName: formValues[1],
						email: formValues[2],
						loginKey: ''
					});
				}
			})();
		},
		save: function () {
			$('.a-save-changes-button').addClass('loading');
			var self = this;
			$.post("./backend/admin.php", {
				func: "admin",
				students: JSON.stringify(self.$root.admin)
			}).done(function () {
				alert("Your changes were saved, but you must sign in again.");
				window.location.href = './login'
			});
		},
		deleteadmin: function (admin) {
			this.$root.admin.splice(admin, 1);
		},
		check: function () {
			$('.a-save-changes-button').html('Save changes');
		}
	}
});

var vm = new Vue({
	el: '#admin-page',
	data: {
		students: Array,
		admin: Array,
		meta: Object,
		notStickered: Array,
		stickeringActiveInit: true
	},
	methods: {
		verify: function () {
			axios
				.get('./backend/verify.php?client=true')
				.then(function (response) {
					if (response.data == '0') {
						window.location.href = 'login/';
					} else if (response.data == '2') {
						window.location.href = 'https://youtu.be/dQw4w9WgXcQ';
					}
				});
		},
		query: function () {
			var self = this;
			axios.get('./backend/admin.php?func=load').then(function (response) {
				//compile list of students
				var studentList = [];
				response.data[0].forEach(student => {
					studentList.push({
						id: student.id,
						firstName: student.first_name,
						lastName: student.last_name,
						email: student.email,
						hs: parseInt(student.hs) == 1 ? true : false,
						loginKey: student.login_key
					});
				});

				self.students = studentList;

				//compile list of admin
				var adminList = [];
				response.data[1].forEach(admin => {
					adminList.push({
						id: admin.id,
						firstName: admin.first_name,
						lastName: admin.last_name,
						email: admin.email,
						loginKey: admin.login_key
					});
				});

				self.admin = adminList;

				//meta

				var metaList = {};
				metaList = {
					stickeringActive: response.data[2][0].stickering_active == '1' ? true : false,
					blacksAllotted: response.data[2][0].blacks_allotted,
					greysAllotted: response.data[2][0].greys_allotted,
					blacksAllottedBlock: response.data[2][0].blacks_allotted_block,
					greysAllottedBlock: response.data[2][0].greys_allotted_block,
					staffPassword: response.data[2][0].staff_password
				};
				self.stickeringActiveInit = response.data[2][0].stickering_active == '1' ? true : false;

				self.meta = metaList;
				console.log(self.meta)

				self.notStickered = response.data[3];
			});
		},
		reminder: function () {
			$('.reminder-button').addClass('loading');
			axios.get('./backend/admin.php?func=reminder')
				.then(function () {
					$('.reminder-button').removeClass('loading');
					$('.reminder-button').html('Reminder email sent!');
				})
				.catch(function () {
					alert("Failed!");
				});
		}
	},
	beforeMount() {
		this.verify();
		this.query();
	},
	mounted() {
		$('.nav-tabs-navigation').show();
	}
});