<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Registration - Google Forms</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f0ebf8;
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
        }
        .form-container {
            width: 100%;
            max-width: 640px;
        }
        .form-header {
            background-color: #fff;
            border-radius: 8px;
            border-top: 10px solid #673ab7;
            padding: 24px;
            margin-bottom: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-title {
            font-size: 24px;
            margin-bottom: 8px;
        }
        .form-desc {
            font-size: 14px;
            color: #5f6368;
        }
        .form-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .question {
            font-size: 16px;
            margin-bottom: 16px;
        }
        .input-text {
            width: 100%;
            border: none;
            border-bottom: 1px solid #dcdcdc;
            padding: 8px 0;
            font-size: 14px;
            outline: none;
            font-family: inherit;
        }
        .input-text:focus {
            border-bottom: 2px solid #673ab7;
            padding-bottom: 7px;
        }
        .btn-submit {
            background-color: #673ab7;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 10px 24px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            margin-top: 12px;
        }
        .btn-submit:hover {
            background-color: #5e35b1;
        }
        .required {
            color: #d93025;
        }
    </style>
</head>
<body>

<div class="form-container">
    <form action="javascript:alert('Thank you! Your mock registration has been recorded successfully. You can close this tab now.'); window.close();">
        <div class="form-header">
            <div class="form-title">Green Campus Event Registration</div>
            <div class="form-desc">Please fill out this form to register for the upcoming event. <br><br><span style="color:red;">*</span> Indicates required question</div>
        </div>

        <div class="form-card">
            <div class="question">Full Name <span class="required">*</span></div>
            <input type="text" class="input-text" placeholder="Your answer" required>
        </div>

        <div class="form-card">
            <div class="question">Student / Staff ID <span class="required">*</span></div>
            <input type="text" class="input-text" placeholder="Your answer" required>
        </div>

        <div class="form-card">
            <div class="question">Email Address <span class="required">*</span></div>
            <input type="email" class="input-text" placeholder="Your answer" required>
        </div>

        <div class="form-card">
            <div class="question">Any Questions or Comments?</div>
            <input type="text" class="input-text" placeholder="Your answer">
        </div>

        <button type="submit" class="btn-submit">Submit</button>
    </form>
</div>

</body>
</html>
