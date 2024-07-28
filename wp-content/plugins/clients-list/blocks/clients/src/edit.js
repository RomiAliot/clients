import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import apiFetch from "@wordpress/api-fetch";
import { useState, useEffect } from "@wordpress/element";
import { Panel, PanelBody, SelectControl, TextControl } from "@wordpress/components";


const edit = props =>{
  const { attributes, setAttributes } = props;
  const blockProps = useBlockProps();
  const [clients, setClients] = useState([]);
  const { nameColumnTitle, lastnameColumnTitle, provinceColumnTitle, numberOfClients, order } = attributes;

  const fetchClients = async () => {
    let path = `/wp/v2/clients?per_page=${numberOfClients}&order=${order}`;
    const newClients = await apiFetch({ path });
    setClients(newClients);
  };

  useEffect(() => {
    fetchClients();
  }, [numberOfClients, order]);

   return(
    <>
      <InspectorControls>
        <Panel>
          <PanelBody title="Options" initialOpen={true}>
            <TextControl
              label="Client Name"
              value={nameColumnTitle}
              onChange={(newNameColumnTitle) => setAttributes({nameColumnTitle: newNameColumnTitle})}
            />
            <TextControl
              label="Client Lastname"
              value={lastnameColumnTitle}
              onChange={(newLastnameColumnTitle) => setAttributes({lastnameColumnTitle: newLastnameColumnTitle})}
            />
            <TextControl
              label="Client Province"
              value={provinceColumnTitle}
              onChange={(newProvinceColumnTitle) => setAttributes({provinceColumnTitle: newProvinceColumnTitle})}
            />
             <TextControl
              label="Number of Clients"
              type="number"
              value={numberOfClients}
              onChange={(newNumberOfClients) => setAttributes({ numberOfClients: parseInt(newNumberOfClients, 10) })}
            />
            <SelectControl
              label="Order"
              value={order}
              options={[
                { label: 'Ascending', value: 'asc' },
                { label: 'Descending', value: 'desc' },
              ]}
              onChange={(newOrder) => setAttributes({ order: newOrder })}
            />
          </PanelBody>
        </Panel>
      </InspectorControls>
      <div {...blockProps} >
        <table >
          <thead>
            <tr class="head">
              <th class="">{nameColumnTitle}</th>
              <th class="">{lastnameColumnTitle}</th>
              <th class="">{provinceColumnTitle}</th>
            </tr>
          </thead>
          <tbody>
            {clients.length > 0 ?
            (clients.map((client) => {
              return(
                <tr class="body" key={client.id}>
                  <td class="">{client.meta.client_name}</td>
                  <td class="">{client.meta.client_lastname}</td>
                  <td class="">{client.meta.client_province}</td>
                </tr>
              );
            })) : (
              <tr>
                <td colSpan="3">No clients found. Add clients in the CPT clients</td>
              </tr>
            )
          }
          </tbody>
        </table>
      </div>
    </>
   )
}

export default edit;