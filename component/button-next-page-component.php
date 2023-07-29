<?php

/**
 * 下一页按钮 HTML代码模板
 * @param string $button_text
 *
 * @return string
 */
function next_page_button( $button_text ) {
	return '
		<button class=" btn btn-lg btn-miku get-next-page w-100 my-3">
			<span class="button-text">' . $button_text . '</span>
			<span class="button-loading spinner-border" style="display: none" role="status" aria-hidden="true"></span>
		</button>';
}