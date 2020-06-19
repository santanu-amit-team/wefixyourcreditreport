<html>
    <head>
        <?php include 'general/__header__.tpl'; ?>
    </head>
    <body>
        <?php perform_body_tag_open_actions(); ?>
        <h1>Thank you!</h1>
        <p>Your Order ID is <b><?= $steps['1']['orderId'] ?></b></p>
        <table>
            <thead>
                <th>Order Details</th>
                <th></th>
            </thead>
            <tbody>
                <tr>
                    <td>Firstname</td>
                    <td><?= $customer['firstName'] ?></td>
                </tr>
                <tr>
                    <td>Lastname</td>
                    <td><?= $customer['lastName'] ?></td>
                </tr>
                <tr>
                    <td>Phone</td>
                    <td><?= $customer['phone'] ?></td>
                </tr>
                <tr>
                    <td>Email Address</td>
                    <td><?= $customer['email'] ?></td>
                </tr>
                <tr>
                    <td>Address</td>
                    <td><?= $customer['shippingAddress1'] ?></td>
                </tr>
                <tr>
                    <td>City</td>
                    <td><?= $customer['shippingCity'] ?></td>
                </tr>
                <tr>
                    <td>Zip</td>
                    <td><?= $customer['shippingZip'] ?></td>
                </tr>
                <tr>
                    <td>State</td>
                    <td><?= $customer['shippingState'] ?></td>
                </tr>
                <tr>
                    <td>Country</td>
                    <td><?= $customer['shippingCountry'] ?></td>
                </tr>
            </tbody>
        </table>
        <?php include 'general/__scripts__.tpl'; ?>
		<?php include 'general/__analytics__.tpl'; ?>
        <?php perform_body_tag_close_actions(); ?>
    </body>
</html>
