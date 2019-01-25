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
						gradYear: student.grad_year
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
