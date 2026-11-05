<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $order->invoice_no }}</title>

    <style>
        /* Base Reset */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Courier New', Courier, Monaco, monospace; /* Clean, authentic POS aesthetic */
            background-color: #f0f0f0; 
            color: #000; 
            font-size: 11px;
            line-height: 1.3;
        }

        /* Helpers */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .text-uppercase { text-transform: uppercase; }
        .mb-1 { margin-bottom: 3px; }
        .mb-2 { margin-bottom: 6px; }
        .mb-3 { margin-bottom: 10px; }
        .d-flex { display: flex; }
        .justify-between { justify-content: space-between; }

        /* Receipt Container */
        .receipt-container {
            width: 80mm; 
            margin: 20px auto;
            background: #fff;
            padding: 4mm 3mm;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        /* Continuous Separator lines popular in retail */
        .dashed-line {
            border-bottom: 1px dashed #000;
            margin: 8px 0;
        }
        .double-dashed-line {
            border-bottom: 3px double #000;
            margin: 8px 0;
        }

        /* Typography */
        .header h2 { font-size: 18px; margin-bottom: 3px; letter-spacing: 1px; }
        .header p { font-size: 11px; }
        .store-msg { font-size: 10px; font-style: italic; margin-top: 2px; }

        /* Retail Table Layout */
        table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        th { border-bottom: 1px dashed #000; padding: 4px 0; font-size: 11px; font-weight: bold; }
        td { padding: 3px 0; font-size: 11px; vertical-align: top; }

        /* Financials & Savings Sections */
        .savings-box {
            border: 1px dashed #000;
            padding: 5px;
            text-align: center;
            font-weight: bold;
            margin: 8px 0;
            font-size: 12px;
        }
        .grand-total {
            font-size: 15px;
            padding: 4px 0;
            margin: 4px 0;
        }

        /* Footer */
        .footer { margin-top: 15px; font-size: 10px; line-height: 1.5; }

        /* Screen Actions */
        .action-buttons { text-align: center; margin-bottom: 20px; margin-top: 20px; }
        .btn {
            background: #222; color: #fff; border: none; padding: 8px 16px;
            border-radius: 4px; cursor: pointer; text-decoration: none;
            font-size: 12px; display: inline-block; margin: 0 5px; font-family: sans-serif;
        }
        .btn:hover { background: #000; }
        .btn-outline { background: transparent; border: 1px solid #222; color: #222; }

        /* Print Override */
        @media print {
            @page { margin: 0; size: auto; }
            body { background: #fff; }
            .receipt-container {
                width: 100%; 
                margin: 0;
                padding: 1mm;
                box-shadow: none;
            }
            .action-buttons { display: none; }
        }
    </style>

    <script>
        window.onload = function() { window.print(); }
    </script>
</head>
<body>

    <div class="action-buttons">
        <button class="btn" onclick="window.print()">Print Receipt</button>
        <button class="btn btn-outline" onclick="window.close()">Close Window</button>
    </div>

    <div class="receipt-container">

        <div class="header text-center mb-2">
            <h2 class="font-bold text-uppercase">52 Bazaar POS</h2>
            <p class="font-bold">A Concern of Retail Advanced Ltd.</p>
            <p>Ka-96/D, Kuril, Vatara, Dhaka-1229</p>
            <p>Mushak-6.3 Bin: 000123456-0101</p>
            <p>Mob: +880 1994282802</p>
            <p class="store-msg">"Fresh Shopping, Better Life"</p>
        </div>

        <div class="double-dashed-line"></div>

        <div class="d-flex justify-between mb-1">
            <span>Bill No: #{{ $order->invoice_no }}</span>
            <span>Date: {{ $order->created_at->format('d/m/y') }}</span>
        </div>
        <div class="d-flex justify-between mb-1">
            <span>Terminal: POS-04</span>
            <span>Time: {{ $order->created_at->format('H:i:s') }}</span>
        </div>
        <div class="d-flex justify-between mb-1">
            <span>Cashier: {{ substr(auth()->user()->name ?? 'Staff', 0, 12) }}</span>
            <span>Cust: {{ substr($order->customer->name ?? 'Cash Customer', 0, 12) }}</span>
        </div>
        @if(!empty($order->customer->phone))
        <div class="mb-1">
            <span>Cust Mob: {{ $order->customer->phone }}</span>
        </div>
        @endif

        <table>
            <thead>
                <tr>
                    <th class="text-left" style="width: 50%">ITEM DESCRIPTION</th>
                    <th class="text-center" style="width: 15%">QTY</th>
                    <th class="text-right" style="width: 15%">RATE</th>
                    <th class="text-right" style="width: 20%">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orderDetails as $index => $item)
                <tr>
                    <td colspan="4" class="text-left" style="padding-top: 4px;">
                        {{ sprintf("%02d", $index + 1) }}. {{ $item->product->name }}
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2, '.', '') }}</td>
                    <td class="text-right">{{ number_format($item->total, 2, '.', '') }}</td>
                </tr>
                
                @if(isset($item->discount) && $item->discount > 0)
                <tr>
                    <td colspan="2" class="text-left" style="font-style: italic; padding-left: 15px; color: #333;">
                        * Promo Discount
                    </td>
                    <td colspan="2" class="text-right" style="font-style: italic;">
                        -{{ number_format($item->discount, 2, '.', '') }}
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>

        <div class="dashed-line"></div>

        <div class="total-section">
            <div class="d-flex justify-between mb-1">
                <span>Sub Total (Net)</span>
                <span>{{ number_format($order->sub_total, 2, '.', '') }}</span>
            </div>

            <div class="d-flex justify-between mb-1">
                <span>Discount</span>
                <span>{{ number_format($order->discount, 2, '.', '') }}</span>
            </div>
            
            <div class="d-flex justify-between mb-1">
                <span>Total VAT / Tax</span>
                <span>{{ number_format($order->vat, 2, '.', '') }}</span>
            </div>

            @if(isset($order->discount_amount) && $order->discount_amount > 0)
            <div class="d-flex justify-between mb-1">
                <span>Special Coupon/Disc.</span>
                <span>-{{ number_format($order->discount_amount, 2, '.', '') }}</span>
            </div>
            @endif

            <div class="double-dashed-line"></div>

            <div class="grand-total d-flex justify-between font-bold">
                <span class="text-uppercase">NET PAYABLE</span>
                <span>{{ number_format($order->total, 2, '.', '') }}</span>
            </div>

            <div class="double-dashed-line"></div>

            <div class="d-flex justify-between mb-1">
                <span>Paid Via [{{ $order->payment_type ?? 'CASH' }}]</span>
                <span>{{ number_format($order->pay_amount, 2, '.', '') }}</span>
            </div>
            
            <div class="d-flex justify-between mb-1">
                <span>Change Returned</span>
                <span>{{ number_format($order->due_amount < 0 ? abs($order->due_amount) : 0, 2, '.', '') }}</span>
            </div>

            <div class="d-flex justify-between mb-1">
                <span>Due</span>
                <span>{{ number_format($order->due_amount > 0 ? abs($order->due_amount) : 0, 2, '.', '') }}</span>
            </div>
        </div>

        @if((isset($order->discount_amount) && $order->discount_amount > 0) || (isset($total_item_discount) && $total_item_discount > 0))
        <div class="savings-box">
            YOU SAVED: {{ number_format(($order->discount_amount ?? 0), 2, '.', '') }} TK!
        </div>
        @endif

        <div class="footer text-center">
            <div class="dashed-line"></div>
            <p class="font-bold">E & O.E.</p>
            <p>Thank you for shopping with us!</p>
            <p>Exchange policy valid within 07 days with original receipt & tag intact (No exchange on Fresh food items).</p>
            <br>
            <p style="letter-spacing: 1px; font-size: 11px;" class="font-bold">* * * {{ $order->invoice_no }} * * *</p>
            <p style="font-size: 8px; margin-top: 4px; color: #555;">Software Powered By: Saddam POS Engine</p>
        </div>
    </div>

</body>
</html>