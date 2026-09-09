/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 *
 * @param {Object} props            Component props.
 * @param {Object} props.attributes Block attributes.
 * @return {Element} Element to render.
 */
export default function save( { attributes } ) {
	const postId = attributes.postId;
	const postTitle = attributes.postTitle;
	const postUrl = attributes.postUrl;

	if ( ! postId ) {
		return null;
	}

	return (
		<p className="dmg-read-more">
			<a href={ postUrl }>Read More: { postTitle }</a>
		</p>
	);
}
