Vue.component('student-table', {
	template: `
    <div>
        <table class='student-table table'>
            <tr>
                <th>First name</th>
                <th>Last name</th>
                <th>Email</th>
                <th>Graduation year</th>
                <th></th>
            </tr>
            <tr v-for="(student,index) in $root.students">
                <td><input type="text" v-model="student.firstName" /></td>
                <td><input type="text" v-model="student.lastName" /></td>
                <td><input type="email" v-model="student.email" /></td>
                <td><input type="number" min="2018" max="2099" step="1" value="2019" v-model="student.gradYear" /></td>
                <td><button class='btn btn-error s-circle' @click='deletestudent(index)'><i class='icon icon-delete'></i></button></td>
            </tr>
        </table>
        <div class='buttons'>
            <button class='btn btn-primary s-circle' @click='addStudent'><i class='icon icon-plus'></i></button>
            <button class='btn btn-primary' @click='save'>Save changes</button>
        </div>
    </div>`,
	methods: {
		addStudent: function() {
			var self = this;

			(async function getFormValues() {
				const { value: formValues } = await Swal.fire({
					title: 'Add a student',
					html:
						'<div class="form-group">' +
						'<input class="form-input" type="text" id="add-fname" placeholder="First name"><br>' +
						'<input class="form-input" type="text" id="add-lname" placeholder="Last name"><br>' +
						'<input class="form-input" type="email" id="add-email" placeholder="Email"><br>' +
						'<input class="form-input" type="number" min="2018" max="2099" step="1" value="2019" id="add-gradyear" placeholder="Graduation year">' +
						'</div>',
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
							$('#add-gradyear').val()
						];
					}
				});

				if (formValues) {
					self.$root.students.push({
						id: '',
						firstName: formValues[0],
						lastName: formValues[1],
						email: formValues[2],
						gradYear: formValues[3],
						loginKey: ''
					});
				}
			})();
		},
		save: function() {
			var self = this;
			axios
				.get(
					'./backend/admin.php?func=students&students=' +
						JSON.stringify(self.$root.students)
				)
				.then(function(response) {
					self.$root.query();
				});
		},
		deletestudent: function(student) {
			this.$root.students.splice(student, 1);
		}
	}
});

var vm = new Vue({
	el: '#admin-page',
	data: {
		students: Array
	},
	methods: {
		verify: function() {
			axios
				.get('./backend/verify.php?client=true')
				.then(function(response) {
					if (response.data == '0') {
						window.location.href = 'login/';
					} else if (response.data == '2') {
						window.location.href = 'https://youtu.be/dQw4w9WgXcQ';
					}
				});
		},
		query: function() {
			var self = this;
			axios.get('./backend/admin.php?func=load').then(function(response) {
				//compile list of students
				var studentList = [];
				response.data.forEach(student => {
					studentList.push({
						id: student.id,
						firstName: student.first_name,
						lastName: student.last_name,
						email: student.email,
						gradYear: student.grad_year,
						loginKey: student.login_key
					});
				});

				self.students = studentList;
			});
		}
	},
	beforeMount() {
		this.verify();
		this.query();
	}
});
