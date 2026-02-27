<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Application Approved</title>
</head>
<body style="margin:0;padding:24px;background:#f4f8fb;font-family:Arial,sans-serif;color:#1f2d3d;">
    @php
        $employmentType = $loanApplication->employment_type instanceof \BackedEnum
            ? $loanApplication->employment_type->value
            : (string) $loanApplication->employment_type;

        $status = $loanApplication->status instanceof \BackedEnum
            ? $loanApplication->status->value
            : (string) $loanApplication->status;
    @endphp

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:680px;margin:0 auto;background:#ffffff;border:1px solid #dbe7ef;border-radius:10px;padding:20px;">
        <tr>
            <td>
                <h2 style="margin:0 0 12px;color:#245a7c;">Your Loan Application Is Approved</h2>
                <p style="margin:0 0 16px;color:#415d73;">
                    Hello {{ $loanApplication->first_name }} {{ $loanApplication->last_name }}, your application has been approved.
                </p>

                <table role="presentation" width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse;">
                    <tr>
                        <td style="border:1px solid #e1eaf1;background:#f8fbfe;font-weight:700;">Applicant Name</td>
                        <td style="border:1px solid #e1eaf1;">{{ $loanApplication->first_name }} {{ $loanApplication->last_name }}</td>
                    </tr>
                    <tr>
                        <td style="border:1px solid #e1eaf1;background:#f8fbfe;font-weight:700;">Email</td>
                        <td style="border:1px solid #e1eaf1;">{{ $loanApplication->email }}</td>
                    </tr>
                    <tr>
                        <td style="border:1px solid #e1eaf1;background:#f8fbfe;font-weight:700;">Age</td>
                        <td style="border:1px solid #e1eaf1;">{{ $loanApplication->age ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td style="border:1px solid #e1eaf1;background:#f8fbfe;font-weight:700;">Phone</td>
                        <td style="border:1px solid #e1eaf1;">{{ $loanApplication->phone }}</td>
                    </tr>
                    <tr>
                        <td style="border:1px solid #e1eaf1;background:#f8fbfe;font-weight:700;">Address</td>
                        <td style="border:1px solid #e1eaf1;">{{ $loanApplication->address ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td style="border:1px solid #e1eaf1;background:#f8fbfe;font-weight:700;">Loan Amount</td>
                        <td style="border:1px solid #e1eaf1;">{{ number_format((float) $loanApplication->loan_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="border:1px solid #e1eaf1;background:#f8fbfe;font-weight:700;">Monthly Income</td>
                        <td style="border:1px solid #e1eaf1;">{{ number_format((float) $loanApplication->monthly_income, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="border:1px solid #e1eaf1;background:#f8fbfe;font-weight:700;">Employment Type</td>
                        <td style="border:1px solid #e1eaf1;">{{ ucwords(str_replace('_', ' ', $employmentType)) }}</td>
                    </tr>
                    @if ($employmentType === 'salaried')
                        <tr>
                            <td style="border:1px solid #e1eaf1;background:#f8fbfe;font-weight:700;">Designation</td>
                            <td style="border:1px solid #e1eaf1;">{{ $loanApplication->designation ?: 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #e1eaf1;background:#f8fbfe;font-weight:700;">Company Name</td>
                            <td style="border:1px solid #e1eaf1;">{{ $loanApplication->company_name ?: 'N/A' }}</td>
                        </tr>
                    @endif
                    @if ($employmentType === 'self_employed')
                        <tr>
                            <td style="border:1px solid #e1eaf1;background:#f8fbfe;font-weight:700;">What You Do for Living</td>
                            <td style="border:1px solid #e1eaf1;">{{ $loanApplication->living_description ?: 'N/A' }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td style="border:1px solid #e1eaf1;background:#f8fbfe;font-weight:700;">Loan Proposal</td>
                        <td style="border:1px solid #e1eaf1;">{{ $loanApplication->loan_proposal ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td style="border:1px solid #e1eaf1;background:#f8fbfe;font-weight:700;">Application Status</td>
                        <td style="border:1px solid #e1eaf1;color:#2f6f0f;font-weight:700;">{{ strtoupper(str_replace('_', ' ', $status)) }}</td>
                    </tr>
                </table>

                <p style="margin:16px 0 0;color:#415d73;">
                    Thank you for using our personal loan system. Our team will contact you with the next steps.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
