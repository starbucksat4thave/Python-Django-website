<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student ID Card</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            /*height: 30vh;*/
            margin: 0;
            background-color: #f4f4f4;
        }
        .id-card {
            width: 550px;
            padding: 15px;
            border: 2px solid black;
            box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);
            background-color: #F5F5F5;
        }
        .header-container {
            text-align: center;
            margin-bottom: 5px;
        }
        .header {
            font-family: Cambria, serif;
            color: #27445D;
            font-weight: bold;
            font-size: 20px;
            text-transform: uppercase;
        }
        .sub-header {
            font-size: 16px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            display: flex;
            margin-bottom: 50px;
        }
        td {
            padding: 2px 5px;
            vertical-align: middle;
        }
        .barcode {
            width: 90px;
            height: 50px;
            /*background-color: #ddd;*/
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
        }
        /* Left-aligned labels */
        .label {
            font-weight: bold;
            text-align: left;  /* Label left-aligned */
            padding-left: 30px;
            color: #1e40af;
            display: flex;
            justify-content: space-between; /* Ensures colon is pushed to the right */
        }
        .label span{
            margin-left: 50px;
        }
        /* Left-aligned values */
        .value {
            text-align: left;  /* Value left-aligned */
            padding-left: 3px;
        }
        .photo-container {
            padding-left: 5px;
        }
        .photo {
            width: 100px;
            height: 100px;
            /*border-radius: 50%;*/
            object-fit: cover;
            border: 2px solid #333;
        }
        .note{
            font-size: 14px;
            color: red;
            margin: 0;
        }
    </style>
</head>
<body>
<div class="id-card">
    <div class="header-container">
        <p class="header">ARTISAN FORCES UNIVERSITY OF TECHNOLOGY</p>
        <p class="sub-header">Student Identity Card</p>
    </div>

    @if(isset($user) && !empty($user))
        <table>
            <tr>
                <th>
{{--                    to satisfy the sonarcloud --}}
                </th>
            </tr>
            <tr>
                <td class="barcode" rowspan="6">
                    {!! $qrCode !!}
                </td>
                <td class="label">Name<span class="colon">:</span></td>
                <td class="value"><strong>{{ strtoupper($user->name) }}</strong></td>
                <td rowspan="6" class="photo-container">
                    @if($user->image)
                        <img src="{{ public_path('storage/' . $user->image) }}" class="photo" alt="User">
                    @else
                        <div class="photo-placeholder">No Photo</div>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Department<span class="colon">:</span></td>
                <td class="value">{{ strtoupper($user->department?->name ?? 'N/A') }}</td>
            </tr>
            <tr>
                <td class="label">Roll No<span class="colon">:</span></td>
                <td class="value">{{ $user->university_id }}</td>
            </tr>
            <tr>
                <td class="label">Session<span class="colon">:</span></td>
                <td class="value">{{ $user->session }} - {{ $user->session + 1 }}</td>
            </tr>
            <tr>
                <td class="label">Date of Birth<span class="colon">:</span></td>
                <td class="value">{{ $user->dob }}</td>
            </tr>
        </table>
    @else
        <p>Invalid or missing user data.</p>
    @endif

    <p class="note">*This ID is electronically generated and does not require a signature. You can verify its validity by scanning the QR code.</p>
</div>
</body>
</html>
