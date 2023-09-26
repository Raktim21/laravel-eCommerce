<!DOCTYPE html>
<head>
    <title>Email Setup</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
        }
        .formbold-mb-3 {
            margin-bottom: 15px;
        }
        #supportCheckbox:checked ~ div span {
            opacity: 1;
        }

        .main {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f1f1f1;
            padding: 48px;
        }

        .wrapper {
            margin: 0 auto;
            max-width: 570px;
            width: 100%;
            background: white;
            /*border: 1px solid #2d3748;*/
            border-radius: 20px;
            padding: 40px;
        }

        .title {
            margin-bottom: 30px;
        }
        .title h2 {
            font-weight: 600;
            font-size: 28px;
            line-height: 34px;
            color: #07074d;
        }
        .title p {
            font-size: 16px;
            line-height: 24px;
            color: #536387;
            margin-top: 12px;
        }

        .input-group {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }
        .input-group > div {
            width: 50%;
        }
        .formbold-form-input {
            text-align: center;
            width: 100%;
            padding: 13px 22px;
            border-radius: 5px;
            border: 1px solid #dde3ec;
            background: #ffffff;
            font-weight: 500;
            font-size: 16px;
            color: #536387;
            outline: none;
            resize: none;
        }
        .formbold-form-input:focus {
            border-color: #6a64f1;
            box-shadow: 0px 3px 8px rgba(0, 0, 0, 0.05);
        }
        .input-label {
            color: #536387;
            font-size: 14px;
            line-height: 24px;
            display: block;
            margin-bottom: 10px;
        }
        .formbold-checkbox-label a {
            margin-left: 5px;
            color: #6a64f1;
        }

        .formbold-btn {
            font-size: 16px;
            border-radius: 5px;
            padding: 14px 25px;
            border: none;
            font-weight: 500;
            background-color: #6a64f1;
            color: white;
            cursor: pointer;
            margin-top: 25px;
        }
        .formbold-btn:hover {
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body>
<div class="main">

    <div class="wrapper">

        <form action="{{ route('configureEmail') }}" method="GET">

            <div class="title">
                <h2 class="">Email Configuration</h2>
                <p>
                    Set up email configuration to seamlessly send emails from your ecommerce site to customers.
                </p>
            </div>

            <div class="input-group">
                <div>
                    <label for="host" class="input-label">
                        Host
                        @if($errors->has('host'))
                            <sup style="color: red">{{ $errors->first('host') }}</sup>
                        @endif
                    </label>
                    <input
                        type="text"
                        name="host"
                        id="host"
                        class="formbold-form-input"
                    />
                </div>
                <div>
                    <label for="port" class="input-label">
                        Port
                        @if($errors->has('port'))
                            <sup style="color: red">{{ $errors->first('port') }}</sup>
                        @endif
                    </label>
                    <input
                        type="text"
                        name="port"
                        id="port"
                        class="formbold-form-input"
                    />
                </div>
            </div>

            <div class="input-group">
                <div>
                    <label for="mailer" class="input-label">
                        Mailer

                        @if($errors->has('mailer'))
                            <sup style="color: red">{{ $errors->first('mailer') }}</sup>
                        @endif
                    </label>
                    <input
                        type="text"
                        name="mailer"
                        id="mailer"
                        class="formbold-form-input"
                    />
                </div>
                <div>
                    <label for="encryption" class="input-label">
                        Encryption

                        @if($errors->has('encryption'))
                            <sup style="color: red">{{ $errors->first('encryption') }}</sup>
                        @endif
                    </label>
                    <input
                        type="text"
                        name="encryption"
                        id="encryption"
                        class="formbold-form-input"
                    />
                </div>
            </div>

            <div class="formbold-mb-3">
                <label for="username" class="input-label">
                    Username

                    @if($errors->has('username'))
                        <sup style="color: red">{{ $errors->first('username') }}</sup>
                    @endif
                </label>
                <input
                    type="text"
                    name="username"
                    id="username"
                    class="formbold-form-input"
                />
            </div>

            <div class="formbold-mb-3">
                <label for="password" class="input-label">
                    Password

                    @if($errors->has('password'))
                        <sup style="color: red">{{ $errors->first('password') }}</sup>
                    @endif
                </label>
                <input
                    type="text"
                    name="password"
                    id="password"
                    class="formbold-form-input"
                />
            </div>

            <div class="input-group">
                <div>
                    <label for="email" class="input-label">
                        Email Address

                        @if($errors->has('email'))
                            <sup style="color: red">{{ $errors->first('email') }}</sup>
                        @endif
                    </label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="formbold-form-input"
                    />
                </div>
                <div>
                    <label for="name" class="input-label">
                        Name

                        @if($errors->has('name'))
                            <sup style="color: red">{{ $errors->first('name') }}</sup>
                        @endif
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        class="formbold-form-input"
                    />
                </div>
            </div>

            <button type="submit" class="formbold-btn">Submit</button>
        </form>
    </div>
</div>
</body>
</html>
