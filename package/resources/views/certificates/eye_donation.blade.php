<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $certificateTitle ?? 'Eye Donation Certificate' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            background-color: #ffffff;
            font-family: 'Helvetica', Arial, sans-serif;
            color: #1e293b;
            font-size: 12px;
            line-height: 1.4;
            position: relative;
            width: 100%;
            height: 100%;
        }

        /* Decorative Corner Ribbons */
        .ribbon-top-left {
            position: absolute;
            top: 14px;
            left: 0;
            width: 220px;
            z-index: 1;
        }
        .ribbon-top-left img {
            width: 220px;
            height: auto;
            display: block;
        }
        .ribbon-bottom-right {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 170px;
            z-index: 1;
        }
        .ribbon-bottom-right img {
            width: 170px;
            height: auto;
            display: block;
        }

        /* Page Container with margin instead of padding to prevent Dompdf table overflow */
        .page-container {
            margin: 22px 36px 14px 38px;
            padding: 0;
            position: relative;
            z-index: 2;
        }

        /* Top Header */
        .top-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .logo-space-cell {
            width: 180px;
            height: 95px;
            vertical-align: top;
        }
        .header-org-cell {
            vertical-align: middle;
            text-align: right;
            padding-top: 24px;
            padding-right: 0px;
        }
        .header-org-title {
            font-family: 'Times-Bold', 'Times New Roman', Times, Georgia, serif;
            font-size: 20px;
            font-weight: 700;
            color: #4a0e1c;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        /* Main Certificate Title */
        .cert-title-section {
            text-align: center;
            margin-top: 6px;
            margin-bottom: 22px;
        }
        .cert-main-heading {
            font-family: 'Times-Bold', 'Times New Roman', Times, Georgia, serif;
            font-size: 27px;
            font-weight: 700;
            color: #5c1122;
            letter-spacing: 2px;
            line-height: 1.15;
            text-transform: uppercase;
        }
        .cert-sub-heading {
            font-family: 'Times-Bold', 'Times New Roman', Times, Georgia, serif;
            font-size: 25px;
            font-weight: 700;
            color: #5c1122;
            letter-spacing: 2px;
            line-height: 1.15;
            text-transform: uppercase;
            margin-top: 4px;
        }

        /* Metadata: Date of Issuance & Certificate Number */
        .meta-section {
            margin-bottom: 16px;
            margin-left: 2px;
        }
        .meta-table {
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 3.5px 0;
            vertical-align: middle;
        }
        .bullet-cell {
            width: 22px;
            text-align: left;
            vertical-align: middle;
        }
        .chevron-icon {
            width: 13px;
            height: auto;
            display: inline-block;
            vertical-align: middle;
        }
        .chevron-fallback {
            font-size: 15px;
            font-weight: bold;
            color: #7a1a2e;
            line-height: 1;
        }
        .meta-label {
            font-family: 'Times-Bold', 'Times New Roman', Times, Georgia, serif;
            font-weight: 700;
            font-size: 12px;
            color: #0f172a;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            padding-right: 12px;
            white-space: nowrap;
        }
        .meta-value-line {
            display: inline-block;
            min-width: 140px;
            font-family: 'Helvetica', Arial, sans-serif;
            font-weight: 500;
            font-size: 12.5px;
            color: #1e293b;
            border-bottom: 1px solid #94a3b8;
            padding-bottom: 1px;
            padding-left: 4px;
            padding-right: 16px;
        }

        /* Unified Donor Section (Details on Left, Photo on Right aligned to top) */
        .donor-unified-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .donor-details-column {
            vertical-align: top;
            padding-left: 2px;
        }
        .donor-badge-wrap {
            margin-bottom: 14px;
        }
        .donor-details-badge {
            display: inline-block;
            background-color: #6b1426;
            color: #ffffff;
            font-family: 'Times-Bold', 'Times New Roman', Times, Georgia, serif;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 5px 22px 4px 22px;
            border-radius: 4px;
        }

        /* Detail Rows */
        .detail-row-table {
            width: 100%;
            border-collapse: collapse;
        }
        .detail-row-table td {
            padding: 4.5px 0;
            vertical-align: middle;
        }
        .detail-content-cell {
            vertical-align: middle;
            font-size: 12px;
            line-height: 1.35;
        }
        .detail-label {
            font-family: 'Times-Bold', 'Times New Roman', Times, Georgia, serif;
            font-weight: 700;
            font-size: 12px;
            color: #0f172a;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            margin-right: 8px;
        }
        .detail-value {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 12.5px;
            color: #1e293b;
            font-weight: 500;
        }

        /* Donor Photo Column */
        .donor-photo-column {
            width: 35%;
            vertical-align: top;
            text-align: right;
            padding-right: 4px;
            padding-top: 0px;
        }
        .donor-photo-frame {
            width: 145px;
            height: 180px;
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            display: inline-block;
            text-align: center;
            overflow: hidden;
        }
        .donor-photo-frame img {
            width: 145px;
            height: 180px;
            display: block;
            margin: 0 auto;
        }

        /* Dividers */
        .section-divider {
            border: 0;
            border-top: 1px solid #cbd5e1;
            margin: 12px 0 14px 0;
            width: 100%;
        }

        /* Signatories Section */
        .signatories-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            margin-bottom: 8px;
        }
        .signatory-cell {
            vertical-align: top;
            padding: 0 10px;
            text-align: center;
        }
        .signature-img-container {
            height: 44px;
            margin-bottom: 6px;
            text-align: center;
        }
        .signature-img-container img {
            max-height: 42px;
            height: 42px;
            width: auto;
            display: block;
            margin: 0 auto;
        }
        .signatory-name {
            font-family: 'Times-Bold', 'Times New Roman', Times, Georgia, serif;
            font-weight: 700;
            font-size: 11px;
            color: #0f172a;
            line-height: 1.25;
        }
        .signatory-desig {
            font-family: 'Times-Roman', 'Times New Roman', Times, Georgia, serif;
            font-size: 9.5px;
            color: #475569;
            line-height: 1.3;
            margin-top: 2px;
        }

        /* Footer */
        .footer-wrap {
            margin-top: 4px;
        }
        .footer-heading {
            font-family: 'Times-Bold', 'Times New Roman', Times, Georgia, serif;
            color: #5c1122;
            font-size: 13.5px;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            font-family: 'Times-Roman', 'Times New Roman', Times, Georgia, serif;
            font-size: 9.5px;
            color: #334155;
            line-height: 1.4;
        }
        .footer-left-col {
            width: 52%;
            vertical-align: top;
            padding-right: 14px;
        }
        .footer-right-col {
            width: 48%;
            vertical-align: top;
            padding-left: 14px;
            border-left: 1px solid #cbd5e1;
        }
        .footer-label {
            font-family: 'Times-Bold', 'Times New Roman', Times, Georgia, serif;
            font-weight: 700;
            color: #0f172a;
        }
    </style>
</head>
<body>
    {{-- Top Left Ribbon with Authentic Circular Logo Badge --}}
    @if(!empty($ribbonTopLeft))
    <div class="ribbon-top-left">
        <img src="{{ $ribbonTopLeft }}" alt="Ribbon & Logo" />
    </div>
    @endif

    {{-- Bottom Right Ribbon --}}
    @if(!empty($ribbonBottomRight))
    <div class="ribbon-bottom-right">
        <img src="{{ $ribbonBottomRight }}" alt="Ribbon" />
    </div>
    @endif

    <div class="page-container">
        {{-- Top Header: Spacing on Left for Logo Badge, Organization Title on Right --}}
        <table class="top-header-table">
            <tr>
                <td class="logo-space-cell">
                    {{-- Positioned image occupies this area --}}
                </td>
                <td class="header-org-cell">
                    <div class="header-org-title">MADHAV NETRAPEDHI</div>
                </td>
            </tr>
        </table>

        {{-- Certificate Main Title --}}
        @php
            $isDonation = (strtolower(trim($pledge['type'] ?? '')) === 'eye donation' || strtolower(trim($pledge['type'] ?? '')) === 'eyedonation');
            $titleLine1 = "EYE DONATION";
            $titleLine2 = $isDonation ? "CERTIFICATE" : "PLEDGE CERTIFICATE";
        @endphp
        <div class="cert-title-section">
            <div class="cert-main-heading">{{ $titleLine1 }}</div>
            <div class="cert-sub-heading">{{ $titleLine2 }}</div>
        </div>

        {{-- Date of Issuance & Certificate Number --}}
        @php
            $formattedIssueDate = !empty($pledge['issueDate']) 
                ? (strtotime($pledge['issueDate']) ? date('d-M-Y', strtotime($pledge['issueDate'])) : $pledge['issueDate']) 
                : date('d-M-Y');
            $certNumDisplay = $pledge['certificateNumber'] ?? 'MNED-10';
        @endphp
        <div class="meta-section">
            <table class="meta-table">
                <tr>
                    <td class="bullet-cell">
                        @if(!empty($chevronBullet))
                            <img src="{{ $chevronBullet }}" class="chevron-icon" alt=">" />
                        @else
                            <span class="chevron-fallback">&#187;</span>
                        @endif
                    </td>
                    <td>
                        <span class="meta-label">DATE OF ISSUANCE :</span>
                        <span class="meta-value-line">{{ $formattedIssueDate }}</span>
                    </td>
                </tr>
                <tr>
                    <td class="bullet-cell">
                        @if(!empty($chevronBullet))
                            <img src="{{ $chevronBullet }}" class="chevron-icon" alt=">" />
                        @else
                            <span class="chevron-fallback">&#187;</span>
                        @endif
                    </td>
                    <td>
                        <span class="meta-label">CERTIFICATE NUMBER :</span>
                        <span class="meta-value-line" style="min-width: 155px;">{{ $certNumDisplay }}</span>
                    </td>
                </tr>
            </table>
        </div>

        {{-- Unified Donor Section: Details on Left, Photo on Right aligned to top --}}
        @php
            $dobFormatted = !empty($pledge['dob']) 
                ? (strtotime($pledge['dob']) ? date('Y-m-d', strtotime($pledge['dob'])) : $pledge['dob']) 
                : 'N/A';
        @endphp
        <table class="donor-unified-table">
            <tr>
                <td class="donor-details-column" style="{{ empty($donorPhoto) ? 'width: 100%;' : 'width: 65%;' }}">
                    {{-- Donor Details Badge --}}
                    <div class="donor-badge-wrap">
                        <div class="donor-details-badge">DONOR'S DETAILS</div>
                    </div>

                    {{-- Rows --}}
                    <table class="detail-row-table">
                        <tr>
                            <td class="bullet-cell">
                                @if(!empty($chevronBullet))<img src="{{ $chevronBullet }}" class="chevron-icon" alt=">" />@else<span class="chevron-fallback">&#187;</span>@endif
                            </td>
                            <td class="detail-content-cell">
                                <span class="detail-label">NAME :</span>
                                <span class="detail-value">{{ $pledge['name'] ?? 'N/A' }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="bullet-cell">
                                @if(!empty($chevronBullet))<img src="{{ $chevronBullet }}" class="chevron-icon" alt=">" />@else<span class="chevron-fallback">&#187;</span>@endif
                            </td>
                            <td class="detail-content-cell">
                                <span class="detail-label">DATE OF BIRTH:</span>
                                <span class="detail-value">{{ $dobFormatted }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="bullet-cell">
                                @if(!empty($chevronBullet))<img src="{{ $chevronBullet }}" class="chevron-icon" alt=">" />@else<span class="chevron-fallback">&#187;</span>@endif
                            </td>
                            <td class="detail-content-cell">
                                <span class="detail-label">CITY :</span>
                                <span class="detail-value">{{ $pledge['city'] ?? 'N/A' }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="bullet-cell">
                                @if(!empty($chevronBullet))<img src="{{ $chevronBullet }}" class="chevron-icon" alt=">" />@else<span class="chevron-fallback">&#187;</span>@endif
                            </td>
                            <td class="detail-content-cell">
                                <span class="detail-label">STATE :</span>
                                <span class="detail-value">{{ $pledge['state'] ?? 'N/A' }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="bullet-cell">
                                @if(!empty($chevronBullet))<img src="{{ $chevronBullet }}" class="chevron-icon" alt=">" />@else<span class="chevron-fallback">&#187;</span>@endif
                            </td>
                            <td class="detail-content-cell">
                                <span class="detail-label">CONTACT NUMBER:</span>
                                <span class="detail-value">{{ $pledge['phone'] ?? 'N/A' }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="bullet-cell">
                                @if(!empty($chevronBullet))<img src="{{ $chevronBullet }}" class="chevron-icon" alt=">" />@else<span class="chevron-fallback">&#187;</span>@endif
                            </td>
                            <td class="detail-content-cell">
                                <span class="detail-label">EMAIL ID :</span>
                                <span class="detail-value">{{ $pledge['email'] ?? 'N/A' }}</span>
                            </td>
                        </tr>
                        @if(!empty($pledge['fm1Name']))
                        <tr>
                            <td class="bullet-cell">
                                @if(!empty($chevronBullet))<img src="{{ $chevronBullet }}" class="chevron-icon" alt=">" />@else<span class="chevron-fallback">&#187;</span>@endif
                            </td>
                            <td class="detail-content-cell">
                                <span class="detail-label">FAMILY MEMBER NAME:</span>
                                <span class="detail-value">{{ $pledge['fm1Name'] }}</span>
                            </td>
                        </tr>
                        @endif
                        @if(!empty($pledge['fm1Contact']))
                        <tr>
                            <td class="bullet-cell">
                                @if(!empty($chevronBullet))<img src="{{ $chevronBullet }}" class="chevron-icon" alt=">" />@else<span class="chevron-fallback">&#187;</span>@endif
                            </td>
                            <td class="detail-content-cell">
                                <span class="detail-label">FAMILY CONTACT NUMBER :</span>
                                <span class="detail-value">{{ $pledge['fm1Contact'] }}</span>
                            </td>
                        </tr>
                        @endif
                    </table>
                </td>

                @if(!empty($donorPhoto))
                <td class="donor-photo-column">
                    <div class="donor-photo-frame">
                        <img src="{{ $donorPhoto }}" alt="Donor Photo" />
                    </div>
                </td>
                @endif
            </tr>
        </table>

        {{-- Divider --}}
        <hr class="section-divider" />

        {{-- Signatories Section --}}
        @if(!empty($signatories) && count($signatories) > 0)
        <table class="signatories-table">
            <tr>
                @foreach($signatories as $sig)
                <td class="signatory-cell" style="width: {{ 100 / count($signatories) }}%;">
                    <div class="signature-img-container">
                        @if(!empty($sig['signatureImage']))
                            <img src="{{ $sig['signatureImage'] }}" alt="Signature" />
                        @endif
                    </div>
                    <div class="signatory-name">{{ $sig['name'] ?? '' }}</div>
                    <div class="signatory-desig">{!! nl2br(e($sig['designation'] ?? '')) !!}</div>
                </td>
                @endforeach
            </tr>
        </table>
        @endif

        {{-- Divider --}}
        <hr class="section-divider" style="margin-top: 4px; margin-bottom: 14px;" />

        {{-- Footer Section --}}
        <div class="footer-wrap">
            <div class="footer-heading">MADHAV NETRALAYA ( SANKALP KENDRA )</div>
            <table class="footer-table">
                <tr>
                    <td class="footer-left-col">
                        <span class="footer-label">Address :</span> {{ !empty($address) ? $address : 'Purushottam Bhavan, Samaj Bhushan Griha Nirman Sahakari Sanstha, Gajanan Nagar T-Point, Ajni, Nagpur – 440015' }}
                    </td>
                    <td class="footer-right-col">
                        <div>
                            <span class="footer-label">Phone :</span> {{ !empty($phone) ? $phone : '0712 - 6785200 / 2253233' }}
                            &nbsp;&nbsp;<span class="footer-label">Mobile :</span> +91 9822208381
                        </div>
                        <div>
                            <span class="footer-label">Email :</span> {{ !empty($email) ? $email : 'info@madhavnetralaya.com' }}
                            &nbsp;&nbsp;<span class="footer-label">Website :</span> {{ !empty($website) ? $website : 'www.madhavnetralaya.org' }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
