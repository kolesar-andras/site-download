[out:json]
[timeout:120]
;
(
node
  ["communication:mobile_phone"]
  ["communication:mobile_phone"!="no"]
  (area:3600021335);
node
  ["man_made"="water_tower"]
  ["communication:mobile_phone"!="no"]
  (area:3600021335);
node
  ["man_made"="mast"]
  ["communication:mobile_phone"!="no"]
  (area:3600021335);
node
  ["man_made"="chimney"]
  ["communication:mobile_phone"!="no"]
  (area:3600021335);
node
  ["tower:type"="communication"]
  ["communication:mobile_phone"!="no"]
  (area:3600021335);
)->.nodes;
(.nodes; rel(bn.nodes)["type"="link"]["link"="microwave"]; >;);
out meta;
