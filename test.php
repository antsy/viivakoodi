<pre>
<?php

require_once('viivakoodi.php');

$tests = [
    ['FI79 4405 2020 0360 82',               4, 4883, 15,       '86851 62596 19897', '100612', '479440520200360820048831500000000868516259619897100612'],
    ['FI58 1017 1000 0001 22',               4,  482, 99,       '55958 22432 94671', '120131', '458101710000001220004829900000000559582243294671120131'],
    ['FI02 5000 4640 0013 02',               4,  693, 80,    '69 87567 20834 35364', '110724', '402500046400013020006938000000069875672083435364110724'],
    ['Sampo FI16 8000 1400 0502 67',         4,  935, 85,    '78 77767 96566 28687', '000000', '416800014000502670009358500000078777679656628687000000'],
    ['Handelsbanken FI73 3131 3001 0000 58', 4,    0,  0,                 '8 68624', '130809', '473313130010000580000000000000000000000000868624130809'],

    ['FI79 4405 2020 0360 82', 5, 4883, 15, 'RF09 8685 1625 9619 897', '100612', '579440520200360820048831509000000868516259619897100612'],
    ['FI79 4405 2020 0360 82', 5, 4883, 15,      '8685 1625 9619 897', '100612', '579440520200360820048831509000000868516259619897100612'], // Checksum missing
    ['Tapiola FI39 3636 3002 0924 92', 5, 1, 3,    'RF66 5907 3839 0', '230311', '539363630020924920000010366000000000000590738390230311'],
];

foreach ($tests as $index => $testSet) {
    $account = BarCode::getNumericIBAN($testSet[0]);
    $version = $testSet[1];
    $euros = $testSet[2];
    $cents = $testSet[3];
    $reference = $testSet[4];
    $dueDate = $testSet[5];
    $expectedBarcode = $testSet[6];

    $bc = new BarCode($account, $version);

    $generatedBarcode = $bc->getBarcode($euros, $cents, $reference, $dueDate);

    ++$index;
    echo "Comparing test set #{$index}\nSum: {$euros},{$cents}€ Acc: $account Ref: $reference, Due: $dueDate\nExpected : {$expectedBarcode}\nActual   : {$generatedBarcode}";
    assert($expectedBarcode == $generatedBarcode);
    echo "\n\n";
}

?>
</pre>
