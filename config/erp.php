<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ICCID Serial Tracking Feature Flag
    |--------------------------------------------------------------------------
    |
    | Jika false (OFF), seluruh produk dikunci ke mode QUANTITY (non-ICCID)
    | dan opsi ICCID disembunyikan pada UI Master Produk.
    | Ubah ERP_ENABLE_ICCID=true di .env saat client siap memakai ICCID.
    |
    */
    'enable_iccid' => filter_var(env('ERP_ENABLE_ICCID', false), FILTER_VALIDATE_BOOLEAN),
];
