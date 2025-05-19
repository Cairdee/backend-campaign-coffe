<!DOCTYPE html>
<html>
<head>
    <title>Invoice Order #{{ $order->id }}</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 8px; }
    </style>
</head>
<body>
    <h2>Nota Pembayaran</h2>
    <p>Nama: {{ $order->user->name }}</p>
    <p>Alamat: {{ $order->address }}</p>
    <p>Tanggal: {{ $order->created_at->format('d M Y') }}</p>

    <h3>Detail Pesanan:</h3>
    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th>Jumlah</th>
                <th>Harga</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
            <tr>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>Rp{{ number_format($item->product->price, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h4>Total: Rp{{ number_format($order->total_price, 0, ',', '.') }}</h4>
</body>
</html>
