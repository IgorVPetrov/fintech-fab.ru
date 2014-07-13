$(document).ready(function () {

	$('button#changeAccountData').click(function () {
		var username = $('#inputUsername').val();
		var url = $('#inputUrl').val();
		var queue = $('#inputQueue').val();
		var password = $('#inputPassword').val();
		var confirmPassword = $('#inputConfirmPassword').val();
		var oldPassword = $('#inputOldPassword').val();
		$.post('account/changeData', {
				username: username,
				url: url,
				queue: queue,
				password: password,
				confirmPassword: confirmPassword,
				oldPassword: oldPassword
			},
			function (data) {
				if (data['errors']) {
					$('#errorUsername').html(data['errors']['username']);
					$('#errorUrl').html(data['errors']['url']);
					$('#errorQueue').html(data['errors']['queue']);
					$('#errorPassword').html(data['errors']['password']);
					$('#errorConfirmPassword').html(data['errors']['confirmPassword']);
					$('#errorOldPassword').html(data['errors']['oldPassword']);
					return;
				}
				location.reload();
			}
		);
	});
});
