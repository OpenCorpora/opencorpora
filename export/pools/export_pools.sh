mkdir tmp

for id in $( perl ../pools.pl /corpus/config.ini | grep -E "[45679]$" | gawk '{ print $1 }' )
do
  echo "id = $id"
  wget "http://opencorpora.org/pools.php?act=samples&pool_id=$id&tabs&mod_ans" --output-document=tmp/pool_$id.tab 
done

cd tmp
zip -9 ../pools.zip pool*.tab
tar -cvjf ../pools.tar.bz2 pool*.tab --remove-files
cd ..
