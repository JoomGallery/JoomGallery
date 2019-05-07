UPDATE `#__updates` SET `detailsurl` = replace(`detailsurl`, 'http://www.en.joomgallery.net/','https://www.joomgalleryfriends.net/') where `detailsurl` like '%www.en.joomgallery.net/%';
UPDATE `#__updates` SET `detailsurl` = replace(`detailsurl`, 'http://www.joomgallery.net/','https://www.joomgalleryfriends.net/') where `detailsurl` like '%www.joomgallery.net/%';

UPDATE `#__update_sites` SET `location` = replace(`location`, 'http://www.en.joomgallery.net/','https://www.joomgalleryfriends.net/') where `location` like '%www.en.joomgallery.net/%';
UPDATE `#__update_sites` SET `location` = replace(`location`, 'http://www.joomgallery.net/','https://www.joomgalleryfriends.net/') where `location` like '%www.joomgallery.net/%';

UPDATE `#__modules` SET `params` = '{"rssurl":"https:\/\/www.joomgalleryfriends.net\/?format=feed&amp;type=rss","rssrtl":0,"rsstitle":1,"rssdate":"0","rssdesc":0,"rssimage":1,"rssitems":3,"rssitemdesc":1,"rssitemdate":0,"word_count":200,"layout":"_:default","moduleclass_sfx":"","cache":1,"cache_time":900,"module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}' where `params` like '%www.joomgallery.net%';
UPDATE `#__modules` SET `params` = '{"rssurl":"https:\/\/www.en.joomgalleryfriends.net\/?format=feed&amp;type=rss","rssrtl":0,"rsstitle":1,"rssdate":"0","rssdesc":0,"rssimage":1,"rssitems":3,"rssitemdesc":1,"rssitemdate":0,"word_count":200,"layout":"_:default","moduleclass_sfx":"","cache":1,"cache_time":900,"module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}' where `params` like '%www.en.joomgallery.net%';

