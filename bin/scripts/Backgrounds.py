import urllib
import urllib2
import json
import MySQLdb
import re
import time
from os import path, mkdir
from datetime import datetime

# Insert an image into the database
def insert(oImage):

    sql = ("""
        INSERT IGNORE INTO images
        (filehash, path, root, width, height, imgur_url, filename)
        VALUES
        (MD5('{6}'), '{1}', '{2}', {3}, {4}, '{5}', '{6}');
    """).format(oImage['filename'], oImage['url'], oImage['root'], oImage['width'], oImage['height'], oImage['imgur_url'], oImage['filename'])
    oDBExec.execute(sql)

# creates directories and saves file
def save(oImage):

    # create root image directory
    strPath = "\\images\\" + oImage["root"]
    if not os.path.isdir(strRoot + strPath):
        os.mkdir(strRoot + strPath)

    # create dimension directory, if it's not created
    strDim = "%sx%s" (str(oImage['width']), + str(oImage['height']))
    strPath += "\\" + strDim
    if not os.path.isdir(strRoot + strPath):
        os.mkdir(strRoot + strPath)
        strPath += "\\" + oImage['root']

        # download file if it doesnt already exist
        if not os.path.isdir(strRoot + strPath):
            try:
                url = oImage['url'].replace("amp;", "")
                f = urllib2.urlopen (url)
                headers = f.info().headers
                type = ""
                for h in headers:
                    if 'Content-Type:' in h:
                        type = h.replace('Content-Type:', '');
                        type = type.replace('\r\n', '');
                        type = type.split('/')[1];

                #  Open our local file for writing
                strPath += '.' + type
                with open(strRoot + strPath, "wb") as local_file:
                    local_file.write(f.read())

                # all went well
                oImage['type'] = "." + type
                oImage['filepath'] = urllib.quote_plus(strPath)
                return oImage

            # handle errors
            except (urllib2.HTTPError, urllib2.URLError) as e:
                print "HTTP Error: %s, url: %s"  % (e.code, url)
    return False

def store(oImage):
    if oImage['root']:
        # oImage = save(oImage)
        oImage['filename'] = oImage['root'] + 'x' + str(oImage['width']) + 'x' + str(oImage['height']) + '.jpeg'
        insert(oImage)

# get data from reddit
def getData(time, count, after):
    after = ('&after=%s' % after) if after else ''
    url = "http://www.reddit.com/r/wallpapers/.json?sort=top&t=%s&limit=%s%s" % (time, str(count), after)
    print 'URL: ' + url
    try:
        strResponse = urllib2.urlopen(url).read()
    except (urllib2.HTTPError, urllib2.URLError) as e:
        print "HTTP Error:", e.code, e.reason
        return False
    return json.loads(strResponse)

def stripImgurUrl(url):
    url = url.split('/')
    imgurid = url[len(url) - 1].split('.')[0]
    return imgurid

def getImages(time, count, after):

    oResponse = getData(time, count, after)
    if not oResponse:
        return False

    aPosts = oResponse['data']['children']
    strCurrentPost = ''
    for oPost in aPosts:

        # get the data
        oData = oPost['data'];

        strID = oData['id']
        strUrl = oData['url'].replace('amp;', '');
        strDomain = oData['domain']

        if 'imgur.com' in strDomain and 'preview' in oPost['data']:


            # get all the image resolutions
            strName = oData['name']
            aImageSource = [oPost['data']['preview']['images'][0]['source']]
            aImageVariants = oPost['data']['preview']['images'][0]['resolutions']
            aImageResolutions = aImageSource + aImageVariants

            for oImage in aImageResolutions:
                oImage['imgur_url'] = strUrl
                root = stripImgurUrl(oImage['imgur_url'])
                root = re.sub('[^0-9a-zA-Z]+', '', root)
                oImage['root'] = root
                oImage['name'] = strName
                oImage['url'] = oImage['url'].replace('amp;', '');
                # store the image
                store(oImage)

            strCurrentPost = strName
    return strCurrentPost

strSubreddit = 'r/earthporn'
strSort = 'top'
period = 'all'
pages = 10

# database connection
oDB = MySQLdb.connect(
    host="localhost",
    user="ima_user",
    passwd="fotbaltym9",
    db="backgrounds")

oDBExec = oDB.cursor()

sleep = 30
tries = 10
strAfter = ''
for page in range(0, pages):
    print '\n----------------------------------------------------------------------------------------------------------'
    print 'Time: %s' % datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    print 'Page: %s' % str(page + 1)

    for t in range(0, tries):

        mSuccess = getImages(period, 100, strAfter)
        if mSuccess:
            strAfter = mSuccess
            oDB.commit()
            print 'Passed on try %s' % str(t + 1)
            break
        elif mSuccess == False:
            print 'Failed on try %s' % str(t + 1)
            oDB.rollback()
            time.sleep(sleep)
        else:
            oDB.rollback
            "Response '%s' invalid. Skipping." % str(mSuccess)
            break
    print '----------------------------------------------------------------------------------------------------------'
    time.sleep(sleep)


