<!-- email body-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{$title}}</title>
</head>
<body style="margin:0px;padding:0px;">
<table align="center" cellpadding="0" cellspacing="0" width="100%" style="background: #ffffff;">
    <tr>
        <td align="center">
            <table cellpadding="0" cellspacing="0" style="width: 580px;">
                <tr>
                    <td align="left" valign="top" style="width: 580px; font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #53565a;">
                        <pre>


    <h4>Hi {{$name}}</h4>



    There is a request to change your login password.


    Didn’t sent request, just ignore this email

    Reset my password:

    <a href="{{$link}}">Reset My Password</a>

    Or

    Paste this link:
    <b>{{$link}}</b>
    to your browser


    Thanks,

    {{$website}}

                        </pre>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
