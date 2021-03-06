<?php namespace CB; ?>
<div class="table-responsive">
    <table class="table">
        <thead>
        <tr>
            <th>Course Date</th>
            <th>NO.</th>
            <th>Course</th>
            <th>Location</th>
            <th>Cost</th>
            <th>Total Cost</th>
            <th>Payment</th>
        </tr>
        </thead>
        <tbody>
            <?php if (count($course_history) > 0) {
                foreach ($course_history as $course) :
            ?>
            <tr>
                <td><?php echo format_course_date(get_df_data($course['coursedate']),
                        get_df_data($course['coursetime']),
                        get_df_data($course['courseendtime'])); ?></td>
                <td>
                    <span class="label label-default"><?php echo get_df_data($course['scheduledcoursesid']); ?></span>
                </td>
                <td><?php echo get_df_data($course['agency']) . ' ' . get_df_data($course['coursetypename']); ?></td>
                <td><?php echo get_df_data($course['locationname']) .
                    '<br>' . get_df_data($course['locationaddress']) .
                    '<br>' . get_df_data($course['locationcity']) . ' ' . get_df_data($course['locationstate']) . ' ' . get_df_data($course['locationzip']); ?></td>
                <td><?php echo sprintf("$%d.00", get_df_data($course['coursecost'])); ?></td>
                <td><?php echo get_df_data($course['coursecost']); ?></td>
                <td class="text-center">
                    <?php
                        $payment_status = strtolower($course['paymentstatus']);
                        if ($payment_status == "paid") {
                            echo '<span class="glyphicon glyphicon-ok"></span><br>';
                            echo '<span class="label label-success">Paid</span>';
                        } else {
                            echo '<a href="#"><button class="btn-u btn-u-orange">Pay Now</button></a>';
                        }
                    ?>
                </td>
            </tr>
            <?php endforeach; } else { ?>
            <tr>
                <td style="text-align: center;" colspan="6"><h2>No course history.</h2></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>