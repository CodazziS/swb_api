rm -rf dist
mkdir dist
cat patch_min.txt > dist/jzodlib.js
find . -not -path "./dist/*" -name '*.js' -exec cat {} + >> dist/jzodlib.js
java -jar /home/disk1/web/compiler.jar --js dist/jzodlib.js --js_output_file dist/jzodlib.min.js
chown -R www-data:www-data .

