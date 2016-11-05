
/**
 * Convenience method for returning the width 'w' and height 'h' of the window
 * 
 * @returns {getWindowSize.utilitiesAnonym$0}
 */
function getWindowSize() {
    return {
        'h': window.innerHeight,
        'w': window.innerWidth
    };
};

/**
 * Clamp the image between to values, keeping the aspect ratio the same.
 * 
 * @param {object} img - must contain a "width" annd "heigh" key
 * @param {int} maxWidth
 * @param {int} maxHeight
 * @returns {object}
 */
function clamp(width, height, maxWidth, maxHeight){

    //get the dimensions
    var window = {w: maxWidth, h: maxHeight};
    var iImgWidth = parseInt(width);
    var iImgHeight = parseInt(height);

    //gotta do this twice to ensure both dimensions are in view
    //this helps when h > 0 and w < 0 or the other way around
    for (i = 0; i < 2; i++) {

        //get the differences and aspect ratio
        var iWDiff = window.w - iImgWidth;
        var iHDiff = window.h - iImgHeight;
        var iRatio = iImgWidth / iImgHeight;

        //but only try twice if one of the dimensions is larger
        //than the screen
        if(i === 1 && (iWDiff > 0 || iHDiff > 0)){
            break;
        }

        //solve for height
        if (Math.abs(iWDiff) > Math.abs(iHDiff)){
            var w = iImgWidth + iWDiff;
            var h = iImgHeight + iWDiff * 1/iRatio;
            iImgWidth = w;
            iImgHeight = h;

        //solve for width
        }else{
            var w = iImgWidth + iHDiff * iRatio;
            var h = iImgHeight + iHDiff;
            iImgHeight = h;
            iImgWidth = w;
        }
    }

    return {w: iImgWidth, h: iImgHeight};
};