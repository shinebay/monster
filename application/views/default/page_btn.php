<div class="page_btn" align="center">
    <a rel="prev" title="上一页" id="prev_page" class="prev_page<?php if($prev==''): ?> none<?php elseif($next!=''):?> left<?php endif; ?>" href="<?php echo $prev; ?>"><span class="left_direction"></span></a>
    <a rel="next" title="下一页" id="next_page" class="next_page<?php if($next==''): ?> none<?php elseif($prev!=''):?> right<?php endif; ?>" href="<?php echo $next; ?>"><span class="right_direction"></span></a>
</div>