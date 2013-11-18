<textarea name="quote[<?= $quote_id ?>][quote_value]" rows="<?= $quote_rows ?>" placeholder="<?= lang("quote_value_placeholder") ?>"><?= htmlspecialchars($quote_value) ?></textarea>
<input name="quote[<?= $quote_id ?>][quote_author]" type="text" value="<?= $quote_author ?>" placeholder="<?= lang("quote_author_placeholder") ?>" />
<input type="hidden" name="<?= $field_name ?>" value="<?= $quote_id ?>" />

