/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';
/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps } from '@wordpress/block-editor';
import { Placeholder } from '@wordpress/components';
/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @param {Object} props            Component props.
 * @param {Object} props.attributes Block attributes.
 * @return {Element} Element to render.
 */
export default function Edit( { attributes } ) {
	const postId = attributes.postId;
	const postTitle = attributes.postTitle;
	const postUrl = attributes.postUrl;

	const blockProps = useBlockProps( { className: 'dmg-read-more' } );

	if ( ! postId ) {
		return (
			<Placeholder
				{ ...blockProps }
				icon="admin-links"
				label={ __( 'DMG Read More', 'dmg-read-more' ) }
				instructions={ __(
					'Search for a post in the block settings sidebar to create a Read More link.',
					'dmg-read-more'
				) }
			/>
		);
	}

	return (
		<p { ...blockProps }>
			<a href={ postUrl }>Read More: { postTitle }</a>
		</p>
	);
}
