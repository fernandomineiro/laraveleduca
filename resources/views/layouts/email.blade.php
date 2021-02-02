<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Educaz</title>
</head>
<body style="background-color: #e9e9e9;">

	<table cellpadding="0" cellspacing="0" bgcolor="#ffffff" width="600" align="center" style="width: 600px; margin: 0 auto; border: 1px solid #ada9a9;">
		<tr>
			<td>
				<table cellspacing="0" cellpadding="0" width="100%">
					<tr>
						<td width="28">&nbsp;</td>
						<td>
							<table cellspacing="0" cellpadding="0" width="100%">
								<tr>
									<td align="center" style="font-family: arial; color: #8c8c8c;">
										@yield('content')
									</td>
								</tr>
							</table>
						</td>
						<td width="28">&nbsp;</td>
					</tr>
				</table>
				<table cellspacing="0" cellpadding="0" width="100%" >
					<p style="padding-bottom: 15px; background-color: #cbbc96; margin-bottom: 0px;"></p>
				</table>
			</td>
		</tr>
	</table>

</body>
</html>
