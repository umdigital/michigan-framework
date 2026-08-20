import { createBlock, rawHandler } from '@wordpress/blocks';

export const transforms = {
    from: [
        {
            type: 'shortcode',
            tag: 'accordion',

            transform: ( { attributes, content } ) => {
                return createBlock( 'michigan-framework/accordion', {
                    title: attributes.title,
                    id: attributes.id,
                    state: attributes.state,
                    content
                } );
            },
        },
        {
            type: 'block',
            blocks: ['core/shortcode'],
            isMatch: function({ text }) {
                return /^\[accordion /.test(text);
            },
            transform: ( { text } ) => {
                return createBlock( 'michigan-framework/accordion', {
                    title: getAttributeValue( 'accordion', 'title', text ),
                    id: getAttributeValue( 'accordion', 'id', text ),
                    state: getAttributeValue( 'accordion', 'state', text )
                }, rawHandler({
                    HTML: getInnerContent( 'accordion', text )
                }) );
            },
        }
    ],
};

/**
 * Get the value for a shortcode attribute, whether it's enclosed in double quotes, single
 * quotes, or no quotes.
 * @SOURCE: https://www.bates.edu/webtech/2019/10/07/switching-from-shortcodes-to-blocks-part2/
 * 
 * @param  {string} tag     The shortcode name
 * @param  {string} att     The attribute name
 * @param  {string} content The text which includes the shortcode
 *                          
 * @return {string}         The attribute value or an empty string.
 */
const getAttributeValue = function(tag, att, content){
    // In string literals, slashes need to be double escaped
    // 
    //    Match  attribute="value"
    //    \[tag[^\]]*      matches opening of shortcode tag 
    //    att="([^"]*)"    captures value inside " and "
    var re = new RegExp(`\\[${tag}[^\\]]* ${att}="([^"]*)"`, 'im');
    var result = content.match(re);
    if( result != null && result.length > 0 )
        return result[1];

    //    Match  attribute='value'
    //    \[tag[^\]]*      matches opening of shortcode tag 
    //    att="([^"]*)"    captures value inside ' and ''
    re = new RegExp(`\\[${tag}[^\\]]* ${att}='([^']*)'`, 'im');
    result = content.match(re);
    if( result != null && result.length > 0 )
        return result[1];

    //    Match  attribute=value
    //    \[tag[^\]]*      matches opening of shortcode tag 
    //    att="([^"]*)"    captures a shortcode value provided without 
        //                     quotes, as in [me color=green]
    re = new RegExp(`\\[${tag}[^\\]]* ${att}=([^\\s]*)\\s`, 'im');
    result = content.match(re);
    if( result != null && result.length > 0 )
       return result[1];
    return false;
};

/**
 * Get the inner content of a shortcode, if any.
 * @SOURCE: https://www.bates.edu/webtech/2019/10/07/switching-from-shortcodes-to-blocks-part2/
 * 
 * @param  {string} tag         The shortcode tag
 * @param  {string} content      The text which includes the shortcode. 
 * @param  {bool}   shouldAutoP  Whether or not to filter return value with autop
 * 
 * @return {string}      An empty string if no inner content, or if the
 *                       shortcode is self-closing (no end tag). Otherwise
 *                       returns the inner content.
 */
const getInnerContent = function(tag, content, shouldAutoP=true) {
   //   \[tag[^\]]*?]    matches opening shortcode tag with or without attributes, (not greedy)
   //   ([\S\s]*?)       matches anything in between shortcodes tags, including line breaks and other shortcodes
   //   \[\/tag]         matches end shortcode tag
   // remember, double escaping for string literals inside RegExp
   const re = new RegExp(`\\[${tag}[^\\]]*?]([\\S\\s]*?)\\[\\/${tag}]`, 'i');
   var result = content.match(re);
   if( result == null || result.length < 1 )
      return '';

   if( shouldAutoP == true)
      result[1] = wp.autop.autop(result[1]);

   return result[1];
};
