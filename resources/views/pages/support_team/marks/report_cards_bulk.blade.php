<!DOCTYPE html>
<html>
<head>
    <title>Bulk Report Cards - {{ $my_class->name }} - {{ $section->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }
        .no-border td {
            border: none;
        }
        .text-left {
            text-align: left;
        }
        .text-right {
            text-align: right;
        }
        .font-bold {
            font-weight: bold;
        }
        .page-break {
            page-break-after: always;
        }
        /* Style for headers to look similar to existing print */
        .header-table td {
            border: none;
        }
        .school-name {
            color: #1b0c80;
            font-size: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    @foreach($students as $sr)
        @php
            $exr = $exam_records->where('student_id', $sr->user_id)->first();
        @endphp
        
        @if($exr)
        <div class="container">
            <table class="header-table">
                <tr>
                    <td style="width: 20%;"><img src="{{ public_path(str_replace(url('/'), '', $s['logo'])) }}" style="max-height : 80px;"></td>
                    <td style="width: 60%; text-align: center;">
                        <span class="school-name">{{ strtoupper(Qs::getSetting('system_name')) }}</span><br/>
                        <i>{{ ucwords($s['address']) }}</i><br/>
                        <strong>REPORT SHEET {{ '('.strtoupper($class_type->name).')' }}</strong>
                    </td>
                    <td style="width: 20%; text-align: right;">
                        <img src="{{ public_path(str_replace(url('/'), '', $sr->user->photo)) }}" width="80" height="80">
                    </td>
                </tr>
            </table>

            <table class="no-border">
                <tr>
                    <td class="text-left"><strong>NAME:</strong> {{ strtoupper($sr->user->name) }}</td>
                    <td class="text-left"><strong>ADM NO:</strong> {{ $sr->adm_no }}</td>
                    <td class="text-left"><strong>CLASS:</strong> {{ strtoupper($my_class->name) }} ({{ $section->name }})</td>
                </tr>
                <tr>
                    <td class="text-left"><strong>EXAM:</strong> {{ strtoupper($ex->name) }}</td>
                    <td class="text-left"><strong>TERM:</strong> {{ $ex->term }}</td>
                    <td class="text-left"><strong>ACADEMIC YEAR:</strong> {{ $ex->year }}</td>
                </tr>
            </table>

            {{-- Marks Table --}}
            <table>
                <thead>
                    <tr>
                        <th rowspan="2">SUBJECTS</th>
                        <th colspan="3">CA</th>
                        <th rowspan="2">EXAM<br>(60)</th>
                        <th rowspan="2">TOTAL<br>(100)</th>
                        <th rowspan="2">GRADE</th>
                        <th rowspan="2">POS</th>
                        <th rowspan="2">REMARKS</th>
                    </tr>
                    <tr>
                        <th>CA1</th>
                        <th>CA2</th>
                        <th>TOT</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($subjects as $sub)
                        @php
                            $mk = $marks->where('student_id', $sr->user_id)->where('subject_id', $sub->id)->first();
                        @endphp
                        <tr>
                            <td class="text-left">{{ $sub->name }}</td>
                            @if($mk)
                                <td>{{ $mk->t1 ?: '-' }}</td>
                                <td>{{ $mk->t2 ?: '-' }}</td>
                                <td>{{ $mk->tca ?: '-' }}</td>
                                <td>{{ $mk->exm ?: '-' }}</td>
                                <td class="font-bold">{{ $mk->$tex ?: '-' }}</td>
                                <td>{{ $mk->grade ? $mk->grade->name : '-' }}</td>
                                <td>{!! ($mk->grade) ? Mk::getSuffix($mk->sub_pos) : '-' !!}</td>
                                <td>{{ $mk->grade ? $mk->grade->remark : '-' }}</td>
                            @else
                                <td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #eee;">
                        <td colspan="5" class="text-right"><strong>TOTAL SCORES & AVERAGE:</strong></td>
                        <td class="font-bold">{{ $exr->total }}</td>
                        <td class="font-bold">{{ $exr->ave }}%</td>
                        <td>POS: {{ Mk::getSuffix($exr->pos) }}</td>
                        <td>POINTS: {{ $exr->points }} | DIV: {{ $exr->division }}</td>
                    </tr>
                </tfoot>
            </table>

            {{-- Annual Results Summary (Only if Term 2) --}}
            @if($ex->term == 2 && $prev_exam_records)
                @php
                    $prev_exr = $prev_exam_records->where('student_id', $sr->user_id)->first();
                    $annual_avg = $prev_exr ? ($exr->ave + $prev_exr->ave) / 2 : $exr->ave;
                @endphp
                <table style="width: 50%; border: 1px solid #000; margin-top: 10px;">
                    <thead>
                        <tr style="background-color: #f2f2f2;"><th colspan="3">ANNUAL PROGRESS SUMMARY</th></tr>
                        <tr><th>TERM 1 (%)</th><th>TERM 2 (%)</th><th>ANNUAL AVG (%)</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $prev_exr ? $prev_exr->ave : '-' }}</td>
                            <td>{{ $exr->ave }}</td>
                            <td class="font-bold">{{ round($annual_avg, 1) }}</td>
                        </tr>
                    </tbody>
                </table>
            @endif

            {{-- Skills and Comments --}}
            <div style="width: 100%; overflow: hidden;">
                <div style="width: 45%; float: left;">
                    <table style="font-size: 10px;">
                        <thead>
                            <tr><th colspan="2">AFFECTIVE & PSYCHOMOTOR</th></tr>
                        </thead>
                        <tbody>
                            @if($skills)
                                @foreach($skills as $sk)
                                    <tr>
                                        <td class="text-left">{{ $sk->name }}</td>
                                        <td>
                                            @php
                                                $val = '';
                                                if($sk->skill_type == 'AF' && $exr->af) {
                                                    $idx = $skills->where('skill_type', 'AF')->values()->search(fn($item) => $item->id == $sk->id);
                                                    $vals = explode(',', $exr->af);
                                                    $val = $vals[$idx] ?? '';
                                                } elseif($sk->skill_type == 'PS' && $exr->ps) {
                                                    $idx = $skills->where('skill_type', 'PS')->values()->search(fn($item) => $item->id == $sk->id);
                                                    $vals = explode(',', $exr->ps);
                                                    $val = $vals[$idx] ?? '';
                                                }
                                            @endphp
                                            {{ $val }}
                                        </div>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>

                <div style="width: 50%; float: right; border: 1px solid #000; padding: 10px; min-height: 100px;">
                    <strong>TEACHER'S COMMENT:</strong><br/>
                    {{ $exr->t_comment ?: '________________________________________________' }}
                    <br/><br/>
                    <strong>PRINCIPAL'S COMMENT:</strong><br/>
                    {{ $exr->p_comment ?: '________________________________________________' }}
                </div>
            </div>

            <div style="margin-top: 20px; text-align: center;">
                <table class="no-border">
                    <tr>
                        <td>____________________<br/>Class Teacher</td>
                        <td>____________________<br/>Principal's Signature</td>
                        <td>____________________<br/>Date</td>
                    </tr>
                </table>
            </div>
        </div>
        
        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
        @endif
    @endforeach
</body>
</html>
