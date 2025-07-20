
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $config['APP_NAME'] . " | " ?> <?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Property Expert' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" integrity="sha512-dPXYcDub/aeb08c63jRq/k6GaKccl256JQy/AnOq7CAnEZ9FzSL9wSbcZkMp4R26vBsMLFYH4kQ67/bbV8XaCQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Lightbox2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.4/dist/css/lightbox.min.css">

    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="manifest" href="/manifest.json">

    <?php if (!empty($customCSS)): ?>
        <style>
            <?= $customCSS ?>
        </style>
    <?php endif; ?>

    <style>
        .property-image {
            height: 200px;
            width: 100%;
            object-fit: cover;
            cursor: pointer;
        }

        /* Move the close button to top-right corner */
        .lb-close {
            top: 20px !important;
            right: 20px !important;
            bottom: auto !important;
            left: auto !important;
            position: absolute !important;
            z-index: 1050 !important;
            width: 32px;
            height: 32px;
            background-size: 32px 32px;
            opacity: 1 !important;
        }

        /* Make sure prev/next buttons are visible */
        .lb-prev,
        .lb-next {
            display: block !important;
            opacity: 1 !important;
            width: 60px;
            height: 100%;
            top: 0;
            z-index: 1040 !important;
            cursor: pointer;
        }

        /* Position prev on left */
        .lb-prev {
            left: 0;
            background: url('https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/images/prev.png') no-repeat center;
        }

        /* Position next on right */
        .lb-next {
            right: 0;
            background: url('https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/images/next.png') no-repeat center;
        }

        /* Prevent Bootstrap resets from hiding lightbox buttons */
        .lb-data .lb-close,
        .lb-nav a {
            background-repeat: no-repeat;
            background-size: contain;
        }
    </style>

</head>