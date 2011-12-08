<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <title>Validation page</title>
  	<link rel="stylesheet" href="css/validationEngine.jquery.css" type="text/css" />
  	<link rel="stylesheet" href="css/messages.css" type="text/css" />
	<script language="JavaScript" src="js/jquery-1.6.min.js" type="text/javascript"></script>
	<script language="JavaScript" src="js/jquery.validationEngine.js" type="text/javascript"></script>
	<script language="JavaScript" src="js/languages/jquery.validationEngine-en.js" type="text/javascript"></script>

	<style type="text/css">
        label{display:inline-block; width: 150px; text-align:right; padding-right:10px }
	</style>
</head>


<body>  <br /><br />
	<div id="message"></div>
	<div id="info"></div>
	<form method="post" id="form" action="action.php" onsubmit="return SendForm(this)">
    	<div>
        	<label>Username</label>
            <input type="text" id="username" name="username" class="validate[required]" value="username" />
		</div>
		<div>
        	<label>Email</label>
            <input type="text" id="email" name="email" class="validate[required,custom[email]]" value="email@mail.com"/>
		</div>
		<div>
			<label></label>
			<input type="submit" value="Submit"/>
		</div>
	</form>


  <script type="text/javascript">

	 function SendForm(frm){
		$.ajax({
			type: "POST",
			url: $(frm).attr('action'),
			data:$(frm).serialize(),
			beforeSend: function(data){return $(frm).validationEngine('validate')},
			success: function(data){ShowResults(data, frm)},
            error: function(data, status){ShowAjaxError(data)},
			dataType: "json"
		});
	   return false;
	 }

	 function ShowResults(data, frm){
		if(data.error){
			ShowError(data.error, data.container);
		}
		if(data.success){
			ShowSuccess(data.success, data.container);
		}
		if(data.redirect){
		window.location = data.redirect;
		}
		if(data.hash){
			window.location.hash=data.hash;
		}
		if(data.refresh){
			location.reload(true);
		}
		if(data.back){
			history.back();
		}
		if(data.list){
			$("#list").html(data.list);
		}
		if(data.hide)
		{
			$(frm).hide();
		}
	 }

	 function ShowError(message, container){
	 	if(container == null) container = "#message";
        $(container).hide().html('<div class="message">'+message+'</div>').fadeIn('slow');
		InitObjects();
	 }
	 function ShowSuccess(message, container){
	 	if(container == null) container = "#message";
        $(container).hide().html('<div class="message success">'+message+'</div>').fadeIn('slow');
		InitObjects();
	 }
	 function ShowAjaxError(data){
        alert("Ajax error occured: \nPage Status: " + data.status +"\nStatus Text: "+data.statusText);
		//console.log(data);
	 }
	 function InitObjects(){
        $(".message").click(function(){$(this).fadeOut('slow')});
     }




  </script>
</body>

</html>