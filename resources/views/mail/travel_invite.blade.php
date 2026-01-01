<table cellspacing="0" cellpadding="0" align="center">
    <tbody>
    <tr>
        <td align="center">
            <table
                style="border-left:1px solid transparent;border-right:1px solid transparent;border-top:1px solid transparent;border-bottom:1px solid transparent"
                bgcolor="#ffffff" align="center">
                <tbody>
                <tr>
                    <td style="background-position: center center" align="left">
                        <table width="100%" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr>
                                <td width="558" align="left">
                                    <table width="100%" cellspacing="0" cellpadding="0">
                                        <tbody>
                                        <tr>
                                            <td align="center" style="font-size:0">
                                                <img src="https://allximik.com/images/photo_1.jpg" width="100%" alt="">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center">
                                                <h2 style="margin-top: 10px">Вас пригласили участвовать в походе</h2>
                                                <h4><i>{{$data['travel_type']}}</i></h4>
                                                <h3>
                                                    <a href="#" style="color: #0a1041"> {{$data['name']}}   </a>
                                                </h3>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center">
                                                <div style="margin-bottom: 50px; margin-top: 50px;">
                                                    <a href="{{route('travel.email.invite.link', ['token'=>$data['token'], 'status' => 'true'])}}"
                                                       class="custom-btn btn-10">approve</a>
                                                    <a href="{{route('travel.email.invite.link', ['token'=>$data['token'], 'status' => 'false'])}}"
                                                       class="custom-btn btn-9">decline</a>
                                                </div>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding-bottom: 20px;">
                        <hr>
                        <img src="https://allximik.com/images/logo.png" width="300px" alt="logo"><br>
                        This email was sent from Allximik Enterprises,<br>
                        LDEFO Boxes 6188 Bruklin, CO, 87155-6828, USA<br>
                        <a href="#" style="color: darkred">Privacy policy</a><br>
                        © 2023 Allximik Enterprises, LLC. All rights reserved.
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
<style>
    .custom-btn {
        width: 110px;
        height: 40px;
        color: #fff;
        border-radius: 8px;
        padding: 10px 25px;
        font-family: 'Lato', sans-serif;
        font-weight: 500;
        background: transparent;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        display: inline-block;
        box-shadow: inset 2px 2px 2px 0px rgba(255, 255, 255, .5),
        7px 7px 20px 0px rgba(0, 0, 0, .1),
        4px 4px 5px 0px rgba(0, 0, 0, .1);
        outline: none;
    }

    .btn-10 {
        background: rgb(22, 9, 240);
        background: linear-gradient(0deg, rgba(22, 9, 240, 1) 0%, rgba(49, 110, 244, 1) 100%);
        color: #fff;
        border: none;
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .btn-10:after {
        position: absolute;
        content: " ";
        top: 0;
        left: 0;
        z-index: -1;
        width: 100%;
        height: 100%;
        transition: all 0.3s ease;
        -webkit-transform: scale(.1);
        transform: scale(.1);
    }

    .btn-10:hover {
        color: #003ec7;
        border: none;
        background: transparent;
    }

    .btn-10:hover:after {
        background: rgb(0, 3, 255);
        background: linear-gradient(0deg, rgba(2, 126, 251, 1) 0%, rgba(0, 3, 255, 1) 100%);
        -webkit-transform: scale(1);
        transform: scale(1);
    }

    .btn-9 {
        background: rgb(240, 9, 9);
        background: linear-gradient(0deg, rgb(240, 9, 9) 0%, rgb(244, 49, 49) 100%);
        color: #fff;
        border: none;
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .btn-9:after {
        position: absolute;
        content: " ";
        top: 0;
        left: 0;
        z-index: -1;
        width: 100%;
        height: 100%;
        transition: all 0.3s ease;
        -webkit-transform: scale(.1);
        transform: scale(.1);
    }

    .btn-9:hover {
        color: #c70000;
        border: none;
        background: transparent;
    }

    .btn-9:hover:after {
        background: rgb(255, 0, 0);
        background: linear-gradient(0deg, rgb(251, 2, 2) 0%, rgb(255, 0, 0) 100%);
        -webkit-transform: scale(1);
        transform: scale(1);
    }
</style>
